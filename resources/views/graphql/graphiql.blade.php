<!DOCTYPE html>
<html>
  <head>
    <title>GraphiQL ‹ Statamic</title>
    <style>
      body {
        height: 100%;
        margin: 0;
        width: 100%;
        overflow: hidden;
      }

      #graphiql {
        height: 100vh;
      }
    </style>
    <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/graphiql/graphiql.min.js" type="application/javascript"></script>
    <link rel="stylesheet" href="https://unpkg.com/graphiql/graphiql.min.css" />
    <script src="https://unpkg.com/@graphiql/plugin-explorer/dist/index.umd.js" crossorigin></script>
    <link rel="stylesheet" href="https://unpkg.com/@graphiql/plugin-explorer/dist/style.css" />
  </head>
  <body>
    <div id="graphiql">Loading...</div>
    <script>
        var xcsrfToken = null;
        const root = ReactDOM.createRoot(document.getElementById('graphiql'));
        const fetcher = GraphiQL.createFetcher({
            url: '{{ $url }}',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'x-csrf-token': xcsrfToken || '{{ csrf_token() }}'
            },
            fetch: async (fetchURL, fetchOptions) => {
                return await fetch(fetchURL, fetchOptions).then((response) => {
                    xcsrfToken = response.headers.get('x-csrf-token');
                    return response;
                });
            },
        });
        const explorerPlugin = GraphiQLPluginExplorer.explorerPlugin();
        root.render(
            React.createElement(GraphiQL, {
                fetcher,
                defaultEditorToolsVisibility: true,
                plugins: [explorerPlugin],
            }),
        );
    </script>
  </body>
</html>
