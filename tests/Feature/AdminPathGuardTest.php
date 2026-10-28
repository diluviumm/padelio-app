<?php

namespace Tests\Feature;

use App\Http\Middleware\BlockEncodedAdminPath;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class AdminPathGuardTest extends TestCase
{
    public function test_encoded_admin_separator_is_rejected(): void
    {
        $request = Request::create('/admin/players', 'GET');
        $request->server->set('REQUEST_URI', '/admin%2Fplayers');

        $this->expectException(NotFoundHttpException::class);
        app(BlockEncodedAdminPath::class)->handle($request, fn () => response('ok'));
    }

    public function test_double_slash_admin_is_rejected(): void
    {
        $request = Request::create('//admin', 'GET');
        $request->server->set('REQUEST_URI', '//admin');

        $this->expectException(NotFoundHttpException::class);
        app(BlockEncodedAdminPath::class)->handle($request, fn () => response('ok'));
    }
}
