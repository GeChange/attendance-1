<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Http\Controllers\PersonalAccessTokenController;

class StudentLoginController extends PersonalAccessTokenController
{
    use AuthenticatesUsers,ValidatesRequests;

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }
        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }
    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        return response('Unauthorized.', 401);
    }

    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );
        $message = \Lang::get('auth.throttle', ['seconds' => $seconds]);
        return response($message, 401);
    }

    public function username()
    {
        return config('passport_login.username', 'email');
    }

    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);
        return $this->authenticated($request, $this->guard()->user());
    }

    protected function authenticated(Request $request, $user)
    {
        return $this->getTokenResponse($request, $user);
    }

    protected function attemptLogin(Request $request)
    {
        return $this->guard()->once(
            $this->credentials($request)
        );
    }

    protected function validateLogin(Request $request)
    {
        $rules = config('passport_login.rules', [
            $this->username() => 'required',
            'password' => 'required',
        ]);
        $this->validate($request, $rules);
    }

    public function logout(Request $request, $tokenId)
    {
        return parent::destroy($request, $tokenId);
    }

    public function user(Request $request)
    {
        return $this->getUserResponse($request, $this->guard()->user());
    }

    protected function getUserResponse(Request $request, $user) {
        return $user;
    }

    public function getTokenName(Request $request, $user)
    {
        return "User #{$user->id} - {$user->email} - {$request->ip()}";
    }

    protected function getTokenResponse(Request $request, $user, array $scopes = [])
    {
        $personalAccessTokenResult = $user->createToken($this->getTokenName($request, $user), $scopes);
        return [
            'accessToken' => $personalAccessTokenResult->accessToken
        ];
    }
}