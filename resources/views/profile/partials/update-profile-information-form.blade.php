<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Informações do Perfil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Aqui estão os seus dados cadastrados no sistema.') }}
        </p>
    </header>

    <div class="mt-6 border-t border-gray-200">
        <dl class="divide-y divide-gray-200">
            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">
                    Nome Completo
                </dt>
                <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <span class="flex-grow">{{ $user->name }}</span>
                </dd>
            </div>
            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">
                    Endereço de E-mail
                </dt>
                <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <span class="flex-grow">{{ $user->email }}</span>
                </dd>
            </div>
            
            @if(auth()->user()->role === 'aluno')
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">
                        CPF
                    </dt>
                    <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <span class="flex-grow">{{ $user->cpf ?? 'Não informado' }}</span>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">
                        R.A.
                    </dt>
                    <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <span class="flex-grow">{{ $user->ra ?? 'Não informado' }}</span>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">
                        Data de Nascimento
                    </dt>
                    <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <span class="flex-grow">{{ $user->data_nascimento ? \Carbon\Carbon::parse($user->data_nascimento)->format('d/m/Y') : 'Não informada' }}</span>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">
                        Curso
                    </dt>
                    <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <span class="flex-grow">{{ $user->curso->nome ?? 'Não definido' }}</span>
                    </dd>
                </div>
            @endif
        </dl>
    </div>
</section>