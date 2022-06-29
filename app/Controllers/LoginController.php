<?php
namespace App\Controllers;


use App\Controllers\BaseController;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;

class LoginController extends BaseController
{
    protected $helpers = ['auth', 'setting'];

    /**
     * Displays the form the login to the site.
     */
    public function loginView()
    {
       
        return view(setting('Auth.views')['login']);
    }

    /**
     * Attempts to log the user in.
     *
     * @return Response|string
     */
    public function loginAction()
    {
        /** @var IncomingRequest $request */
        $request = service('request');

        $credentials             = $request->getPost(setting('Auth.validFields'));
        $credentials             = array_filter($credentials);
        $credentials['password'] = $request->getPost('password');
        $remember                = (bool) $request->getPost('remember');

        // Attempt to login
        $result = auth('session')->remember($remember)->attempt($credentials);
        if (! $result->isOK()) {
            return redirect()->route('login')->withInput()->with('error', $result->reason());
        }

        $user = $result->extraInfo();

        // If an action has been defined for login, start it up.
        $actionClass = setting('Auth.actions')['login'] ?? null;
        if (! empty($actionClass)) {
            return redirect()->to(route_to('auth-action-show'))->withCookies();
        }

        return redirect()->to(config('Auth')->loginRedirect())->withCookies();
    }

    /**
     * Logs the current user out.
     *
     * @return Response|string
     */
    public function logoutAction()
    {
        $user = auth()->user();

        auth()->logout();

        return redirect()->to(config('Auth')->logoutRedirect());
    }
}
