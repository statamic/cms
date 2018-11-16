<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\URL;
use Statamic\API\User;
use Statamic\API\Email;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\API\Fieldset;
use Illuminate\Http\Request;
use Statamic\Fields\Validation;
use Statamic\Auth\PasswordReset;
use Statamic\Extensions\Pagination\LengthAwarePaginator;

class UsersController extends CpController
{
    use GetsTaxonomiesFromFieldsets;

    /**
     * @var \Statamic\Contracts\Data\Users\User|\Statamic\Contracts\Permissions\Permissible
     */
    private $user;

    /**
     * Redirect to the current user's edit page
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function account()
    {
        return redirect()->route('user.edit', User::getCurrent()->username());
    }

    public function index(Request $request)
    {
        $this->access('users:view');

        if ($request->wantsJson()) {
            return $this->json();
        }

        return view('statamic::users.index');
    }

    /**
     * Get users as JSON
     *
     * @return array
     */
    public function json()
    {
        $users = User::all()->supplement('checked', function () {
            return false;
        });

        /**
         * Since the `name` field is a computed value, sorting doesn't seem
         * trigger a change on it. So it's better to sort it with the first
         * name when the name is being used.
         */
        $sort = request('sort', 'username');
        $multisort = ($sort == 'name') ? 'first_name' : $sort;
        $users = $users->multisort($multisort . ':' . request('order'));

        // Set up the paginator, since we don't want to display all the users.
        $totalUserCount = $users->count();
        $perPage = Config::get('statamic.cp.pagination_size');
        $currentPage = (int) $this->request->page ?: 1;
        $offset = ($currentPage - 1) * $perPage;
        $users = $users->slice($offset, $perPage);
        $paginator = new LengthAwarePaginator($users, $totalUserCount, $perPage, $currentPage);

        return [
            'data'   => $users->toArray(),
            'meta' => [
                'sortColumn' => $sort,
                'columns' => [
                    ['label' => 'name', 'field' => 'name'],
                    ['label' => 'username', 'field' => 'username'],
                    ['label' => 'email', 'field' => 'email'],
                ],
            ],
            'pagination' => [
                'totalItems' => $totalUserCount,
                'itemsPerPage' => $perPage,
                'totalPages'    => $paginator->lastPage(),
                'currentPage'   => $paginator->currentPage(),
                'prevPage'      => $paginator->previousPageUrl(),
                'nextPage'      => $paginator->nextPageUrl(),
                'segments'      => array_get($paginator->renderArray(), 'segments')
            ]
        ];
    }

    /**
     * Create a new user
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('users:create');

        $fieldset = 'user';

        $data = $this->populateWithBlanks($fieldset);

        return view('statamic::publish', [
            'extra'             => [],
            'is_new'            => true,
            'content_data'      => $data,
            'content_type'      => 'user',
            'fieldset'          => $fieldset,
            'title'             => trans('cp.create_a_user'),
            'uuid'              => null,
            'url'               => null,
            'parent_url'        => null,
            'slug'              => null,
            'status'            => null,
            'uri'               =>  null,
            'locale'            => default_locale(),
            'is_default_locale' => true,
            'locales'           => [],
            'taxonomies'        => $this->getTaxonomies(Fieldset::get($fieldset))
        ]);
    }

    public function edit($id)
    {
        $this->user = $user = User::find($id);

        // Users can always manage their data // TODO
        if ($user !== User::getCurrent()) {
            $this->authorize('users:view');
        }

        $values = $user->blueprint()
            ->fields()
            ->addValues($user->data())
            ->preProcess()
            ->values();


        if (Config::get('statamic.users.login_type') === 'email') {
            $values['email'] = $user->email();
        } else {
            $values['username'] = $user->username();
        }

        $values['roles'] = $user->roles()->map(function ($role) {
            return $role->uuid();
        });
        $values['user_groups'] = $user->groups()->keys();
        $values['status'] = $user->status();

        return view('statamic::users.edit', [
            'user' => $user,
            'values' => $values
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        $this->authorize('edit', $user);

        $fields = $user->blueprint()->fields()->addValues($request->all())->process();

        $validation = (new Validation)->fields($fields)->withRules([
            'username' => 'required', // TODO: Needs to be more clever re: different logic for email as login
        ]);

        $request->validate($validation->rules());

        foreach (array_except($fields->values(), 'username') as $key => $value) {
            $user->set($key, $value);
        }

        $user
            ->username($request->username)
            ->save();

        return response('', 204);
    }

    /**
     * Delete a user
     *
     * @return array
     */

    public function delete()
    {
        $this->authorize('users:delete');

        $ids = Helper::ensureArray($this->request->input('ids'));

        foreach ($ids as $id) {
            User::find($id)->delete();
        }

        return ['success' => true];
    }

    /**
     * Create the data array, populating it with blank values for all fields in
     * the fieldset, then overriding with the actual data where applicable.
     *
     * @param string|\Statamic\Contracts\Data\Users\User $arg
     * @return array
     */
    private function populateWithBlanks($arg)
    {
        // Get a fieldset and data
        if ($arg instanceof \Statamic\Contracts\Data\Users\User) {
            $fieldset = $arg->fieldset();
            $data = $arg->processedData();
        } else {
            $fieldset = Fieldset::get($arg);
            $data = [];
        }

        // Get the fieldtypes
        $fieldtypes = collect($fieldset->fieldtypes())->keyBy(function ($ft) {
            return $ft->getName();
        });

        // Build up the blanks
        $blanks = [];
        foreach ($fieldset->fields() as $name => $config) {
            if (! $default = array_get($config, 'default')) {
                $default = $fieldtypes->get($name)->blank();
            }

            $blanks[$name] = $default;
        }

        return array_merge($blanks, $data);
    }

    public function getResetUrl($username)
    {
        $user = User::whereUsername($username);

        // Users can reset their own password
        if ($user !== User::getCurrent()) {
            $this->authorize('super');
        }

        $resetter = new PasswordReset;

        $resetter->user($user);

        return [
            'success' => true,
            'url' => $resetter->url()
        ];
    }

    public function sendResetEmail($username)
    {
        $user = User::whereUsername($username);

        if (! $user->email()) {
            return ['success' => false];
        }

        $resetter = new PasswordReset;

        $resetter->user($user);

        $resetter->send();

        return ['success' => true];
    }
}
