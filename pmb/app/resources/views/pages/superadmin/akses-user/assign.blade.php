<x-superadmin-layout>
    <div class="container grid px-6 mx-auto" x-data="{ editName: '', editId: '', editRoles: '', editPermissions: '', isModalOpen: false, isModalEditOpen: false, isModalHapusOpen: false }">
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Management User
        </h2>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Terjadi Kesalahan!</strong>
                <ul class="list-disc pl-5 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                role="alert">
                <strong class="font-bold">Berhasil!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Gagal!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- User Section -->
        <div>
            <button @click="isModalOpen = true"
                class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                Tambah User
            </button>
        </div>
        <div class="w-full overflow-hidden border rounded-lg shadow-xs my-3">
            <div class="w-full overflow-x-auto ">
                <table class="w-full whitespace-no-wrap ">
                    <thead>
                        <tr
                            class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Permission</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                        @forelse ($users as $user)
                            <tr class="text-gray-700 dark:text-gray-400">
                                <td class="px-4 py-3 text-sm">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $user->name }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $user->email }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $user->roles->pluck('name')->implode(', ') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $user->permissions->pluck('name')->implode(', ') }}

                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center space-x-4 text-sm">
                                        <button
                                            @click="editName = '{{ $user->name }}'; editId = '{{ $user->id }}'; editRoles = '{{ $user->roles->pluck('id')->implode(', ') }}'; editPermissions = '{{ $user->permissions->pluck('id')->implode(', ') }}'; isModalEditOpen = true;"
                                            class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray"
                                            aria-label="Edit">
                                            <svg class="w-5 h-5" aria-hidden="true" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z">
                                                </path>
                                            </svg>
                                        </button>
                                       
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center px-4 py-3 text-sm">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Tambah user -->
        <div x-show="isModalOpen" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center">
            <div x-show="isModalOpen" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 transform translate-y-1/2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform translate-y-1/2" @click.away="closeModal"
                @keydown.escape="closeModal"
                class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl"
                role="dialog" id="modal">
                <header class="flex justify-end">
                    <button
                        class="inline-flex items-center justify-center w-6 h-6 text-gray-400 transition-colors duration-150 rounded dark:hover:text-gray-200 hover:focus:outline-none"
                        aria-label="close" @click="isModalOpen = false">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" role="img" aria-hidden="true">
                            <path
                                d="M14.348 5.652a.5.5 0 10-.707-.707L10 8.586 6.36 4.945a.5.5 0 00-.707.707L9.293 10l-3.64 3.64a.5.5 0 00.707.707L10 11.414l3.64 3.64a.5.5 0 00.707-.707L10.707 10l3.64-3.64z">
                            </path>
                        </svg>
                    </button>
                </header>
                <div class="mt-4 mb-6">
                    <p class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">
                        Tambah User
                    </p>
                    <form action="{{ route('superadmin.akses-user.user.store') }}" method="POST">
                        @csrf
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Nama User</span>
                            <input type="text"
                                class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                                placeholder="Nama User" name="name" required />
                        </label>
                        <label class="block text-sm mt-3">
                            <span class="text-gray-700 dark:text-gray-400">Email</span>
                            <input type="email"
                                class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                                placeholder="Email User" name="email" required />
                        </label>
                        <label class="block text-sm mt-3">
                            <span class="text-gray-700 dark:text-gray-400">Password</span>
                            <input type="password"
                                class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                                placeholder="Password" name="password" required />
                        </label>
                        <label class="block text-sm mt-3">
                            <span class="text-gray-700 dark:text-gray-400">Assign Role</span>
                            <select name="roles[]" multiple required
                                class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-multiselect">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}">
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block text-sm mt-3">
                            <span class="text-gray-700 dark:text-gray-400">Assign Permission</span>
                            <select name="permissions[]" multiple
                                class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-multiselect">
                                @foreach ($permissions as $permission)
                                    <option value="{{ $permission->name }}">
                                        {{ $permission->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <footer class="flex justify-end mt-6">
                            <button type="button" @click="isModalOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-transparent rounded-lg hover:bg-gray-300 focus:outline-none focus:shadow-outline-gray">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-lg ml-4 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                                Simpan
                            </button>
                        </footer>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Edit Role dan permission -->
        <div x-show="isModalEditOpen" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-30 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center">
            <div x-show="isModalEditOpen" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 transform translate-y-1/2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform translate-y-1/2" @click.away="closeModal"
                @keydown.escape="closeModal"
                class="w-full px-6 py-4 overflow-hidden bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 sm:max-w-xl"
                role="dialog" id="modal">
                <header class="flex justify-end">
                    <button
                        class="inline-flex items-center justify-center w-6 h-6 text-gray-400 transition-colors duration-150 rounded dark:hover:text-gray-200 hover:focus:outline-none"
                        aria-label="close" @click="isModalEditOpen = false">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" role="img"
                            aria-hidden="true">
                            <path
                                d="M14.348 5.652a.5.5 0 10-.707-.707L10 8.586 6.36 4.945a.5.5 0 00-.707.707L9.293 10l-3.64 3.64a.5.5 0 00.707.707L10 11.414l3.64 3.64a.5.5 0 00.707-.707L10.707 10l3.64-3.64z">
                            </path>
                        </svg>
                    </button>
                </header>
                <div class="mt-4 mb-6">
                    <p class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">
                        Edit Role
                    </p>
                    <form :action="'{{ url('superadmin/akses-user/assign') }}/' + editId" method="POST">
                        @csrf
                        @method('PUT')
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Assign Role</span>
                            <select name="roles[]" multiple
                                class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-multiselect">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}"
                                        x-bind:selected="editRoles.includes({{ $role->id }})">
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block text-sm mt-3">
                            <span class="text-gray-700 dark:text-gray-400">Assign Permission</span>
                            <select name="permissions[]" multiple
                                class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-multiselect">
                                @foreach ($permissions as $permission)
                                    <option value="{{ $permission->name }}"
                                        x-bind:selected="editPermissions.includes({{ $permission->id }})">
                                        {{ $permission->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <footer class="flex justify-end mt-6">
                            <button type="button" @click="isModalEditOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-transparent rounded-lg hover:bg-gray-300 focus:outline-none focus:shadow-outline-gray">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-lg ml-4 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                                Simpan
                            </button>
                        </footer>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-superadmin-layout>
