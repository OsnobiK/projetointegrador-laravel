<?php

namespace App\Http\Controllers;

use App\Models\User; // Importe o modelo User
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Para criptografar a senha
use Illuminate\Validation\ValidationException; // Para tratar erros de validação
use Illuminate\Support\Facades\Auth; // Para fazer o login

class AuthController extends Controller
{
    /**
     * Exibe o formulário de login/cadastro.
     *
     * @return \Illuminate\View\View
     */
    public function showAuthForm()
    {
        return view('auth'); // Retorna a view auth.blade.php
    }

    /**
     * Processa o cadastro de um novo usuário.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        try {
            // Validação dos dados do formulário
            $request->validate([
                'username' => 'required|string|max:255|unique:users',
                'email' => 'required|string|email|max:255|unique:users',
                'cpf' => 'required|string|size:11|unique:users', // CPF com 11 dígitos
                'telefone' => 'nullable|string|max:15',
                'password' => 'required|string|min:8|confirmed', // 'confirmed' exige um campo 'password_confirmation'
                'term' => 'accepted', // Requer que o checkbox de termos seja aceito
            ], [
                'username.unique' => 'Este nome de usuário já está em uso.',
                'email.unique' => 'Este e-mail já está em uso.',
                'cpf.unique' => 'Este CPF já está cadastrado.',
                'cpf.size' => 'O CPF deve ter 11 dígitos.',
                'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
                'password.confirmed' => 'A confirmação de senha não corresponde.',
                'term.accepted' => 'Você deve aceitar os Termos de Serviço para se cadastrar.',
            ]);

            // Cria o novo usuário no banco de dados
            User::create([
                'username' => $request->username,
                'email' => $request->email,
                'cpf' => $request->cpf,
                'telefone' => $request->telefone,
                'password' => Hash::make($request->password), // Criptografa a senha
            ]);

            // Redireciona para alguma página de sucesso ou para a mesma tela com mensagem
            return redirect()->route('auth.form')->with('success', 'Cadastro realizado com sucesso! Agora você pode fazer login.');

        } catch (ValidationException $e) {
            // Em caso de falha na validação, redireciona de volta com os erros e inputs
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // Captura outras exceções genéricas
            return redirect()->back()->with('error', 'Ocorreu um erro ao tentar cadastrar. Tente novamente mais tarde.')->withInput();
        }
    }

    /**
     * Processa o login do usuário. (Implementação básica)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'], // Assumindo que o login pode ser por username, CPF ou email
            'password' => ['required'],
        ]);

        // Tenta autenticar pelo username
        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard')->with('success', 'Login realizado com sucesso!');
        }

        // Tenta autenticar pelo email, se não for username
        if (Auth::attempt(['email' => $credentials['username'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard')->with('success', 'Login realizado com sucesso!');
        }

        // Tenta autenticar pelo CPF, se não for username nem email
        if (Auth::attempt(['cpf' => $credentials['username'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard')->with('success', 'Login realizado com sucesso!');
        }

        return back()->withErrors([
            'username' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ])->onlyInput('username');
    }

    /**
     * Faz logout do usuário.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/auth')->with('success', 'Você foi desconectado.');
    }
}