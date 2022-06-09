<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof UnauthorizedHttpException) {
            return response()->json(['code'=>404,'msg'=>'请求失败', 'data'=>[]]);
        } elseif ($exception instanceof ValidationException) {
            return response()->json(['code'=>404,'msg'=>'请求失败', 'data'=>[]]);
        } elseif ($exception instanceof MethodNotAllowedHttpException) {
            #请求方法不被允许
            return response()->json(['code'=>404,'msg'=>'请求失败', 'data'=>[]]);
        } elseif ($exception instanceof ErrorException) {
            return response()->json(['code'=>404,'msg'=>'请求失败', 'data'=>[]]);
        } elseif ($exception instanceof Throwable) {
            return response()->json(['code'=>404,'msg'=>'请求失败', 'data'=>[]]);
        }
        return parent::render($request, $exception);
    }
}
