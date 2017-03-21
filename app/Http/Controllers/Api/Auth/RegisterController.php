<?php
/**
 * Created by PhpStorm.
 * User: RamS-NSET
 * Date: 3/21/2017
 * Time: 4:25 AM
 */

namespace App\Http\Controllers\Api\Auth;

use App\Events\Api\Auth\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Repositories\Frontend\Access\User\UserRepository;
use Illuminate\Foundation\Auth\RegistersUsers;
use Tymon\JWTAuth\Facades\JWTAuth;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * @var UserRepository
     */
    protected $user;

    /**
     * RegisterController constructor.
     *
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    /**
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        if(!$request->validate()){
            return response()->json(['errors'=>$request->errors]);
        }

        if (config('access.users.confirm_email')) {

            $user = $this->user->create($request->all());
            event(new UserRegistered($user));

            return response()->json(['needsVerification'=>true,'message'=>trans('exceptions.frontend.auth.confirmation.created_confirm')]);
        } else {
            $token  = JWTAuth::fromUser($user);

            event(new UserRegistered(access()->user()));
            return response()->json(['token'=>$token]);
        }

    }

}