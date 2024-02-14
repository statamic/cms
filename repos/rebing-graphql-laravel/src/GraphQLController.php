<?php

declare(strict_types = 1);
namespace Rebing\GraphQL;

use GraphQL\Server\OperationParams as BaseOperationParams;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Laragraph\Utils\RequestParser;
use Rebing\GraphQL\Support\OperationParams;

class GraphQLController extends Controller
{
    public function query(Request $request, RequestParser $parser, Repository $config, GraphQL $graphql): JsonResponse
    {
        $routePrefix = $config->get('graphql.route.prefix', 'graphql');
        $schemaName = $this->findSchemaNameInRequest($request, "/$routePrefix") ?: $config->get('graphql.default_schema', 'default');

        $operations = $parser->parseRequest($request);

        $headers = $config->get('graphql.headers', []);
        $jsonOptions = $config->get('graphql.json_encoding_options', 0);

        $isBatch = \is_array($operations);

        $supportsBatching = $config->get('graphql.batching.enable', true);

        if ($isBatch && !$supportsBatching) {
            $data = $this->createBatchingNotSupportedResponse($request->input());

            return response()->json($data, 200, $headers, $jsonOptions);
        }

        $data = Helpers::applyEach(
            function (BaseOperationParams $baseOperationParams) use ($schemaName, $graphql): array {
                $operationParams = new OperationParams($baseOperationParams);

                return $graphql->execute($schemaName, $operationParams);
            },
            $operations
        );

        return response()->json($data, 200, $headers, $jsonOptions);
    }

    /**
     * In case batching is not supported, send an error back for each batch
     * (with a hardcoded limit of 100).
     *
     * The returned format still matches the GraphQL specs
     *
     * @param array<string,mixed> $input
     * @return array<array{errors:array<array{message:string}>}>
     */
    protected function createBatchingNotSupportedResponse(array $input): array
    {
        $count = min(\count($input), 100);

        $data = [];

        for ($i = 0; $i < $count; $i++) {
            $data[] = [
                'errors' => [
                    [
                        'message' => 'Batch request received but batching is not supported',
                    ],
                ],
            ];
        }

        return $data;
    }

    protected function findSchemaNameInRequest(Request $request, string $routePrefix): ?string
    {
        $path = $request->getPathInfo();

        if (!Str::startsWith($path, $routePrefix)) {
            return null;
        }

        return trim(Str::after($path, $routePrefix), '/');
    }
}
