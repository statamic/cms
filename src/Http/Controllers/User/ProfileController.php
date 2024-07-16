<?php

namespace Statamic\Http\Controllers\User;

use Statamic\Facades\User;
use Statamic\Http\Requests\UserProfileRequest;

class ProfileController
{
    public function __invoke(UserProfileRequest $request)
    {
        $user = User::current();

        if ($request->email) {
            $user->email($request->email);
        }

        foreach ($request->processedValues() as $key => $value) {
            $user->set($key, $value);
        }

        $user->save();

        return $this->successfulResponse();
    }

    private function successfulResponse()
    {
        $response = request()->has('_redirect') ? redirect(request()->get('_redirect')) : back();

        if (request()->ajax() || request()->wantsJson()) {
            return response([
                'success' => true,
                'redirect' => $response->getTargetUrl(),
            ]);
        }

        session()->flash('user.profile.success', __('Update successful.'));

        return $response;
    }
}
