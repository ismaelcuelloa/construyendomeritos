<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class DiagnoseEmailSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diagnose:email {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnostica el sistema de correo y restablecimiento de contraseña';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 DIAGNÓSTICO DEL SISTEMA DE CORREO');
        $this->newLine();

        // 1. Verificar configuración de correo
        $this->info('1️⃣ Verificando configuración de correo...');
        $this->table(
            ['Configuración', 'Valor'],
            [
                ['Mailer', config('mail.default')],
                ['Host', config('mail.mailers.smtp.host') ?? 'N/A'],
                ['Puerto', config('mail.mailers.smtp.port') ?? 'N/A'],
                ['Usuario', config('mail.mailers.smtp.username') ?? 'N/A'],
                ['From Address', config('mail.from.address')],
                ['From Name', config('mail.from.name')],
                ['APP_URL', config('app.url')],
            ]
        );
        $this->newLine();

        // 2. Verificar configuración de cola
        $this->info('2️⃣ Verificando configuración de cola...');
        $queueConnection = config('queue.default');
        $this->line("   Conexión de cola: <fg=yellow>{$queueConnection}</>");

        if ($queueConnection !== 'sync') {
            $pendingJobs = DB::table('jobs')->count();
            $this->line("   Trabajos pendientes: <fg=yellow>{$pendingJobs}</>");

            if ($pendingJobs > 0) {
                $this->warn('   ⚠️  Hay trabajos en cola. Ejecuta: php artisan queue:work');
            }
        }
        $this->newLine();

        // 3. Probar envío de correo básico
        $this->info('3️⃣ Probando envío de correo básico...');
        $testEmail = $this->argument('email') ?? $this->ask('¿A qué correo enviar la prueba?');

        if ($testEmail) {
            try {
                Mail::raw('Este es un correo de prueba del sistema de diagnóstico.', function ($message) use ($testEmail) {
                    $message->to($testEmail)
                        ->subject('Test - Sistema de Diagnóstico');
                });
                $this->info("   ✅ Correo de prueba enviado a: {$testEmail}");
            } catch (\Exception $e) {
                $this->error('   ❌ Error al enviar correo: '.$e->getMessage());
            }
            $this->newLine();

            // 4. Verificar usuario
            $this->info('4️⃣ Verificando usuario en base de datos...');
            $user = User::where('email', $testEmail)->first();

            if ($user) {
                $this->info('   ✅ Usuario encontrado');
                $this->table(
                    ['Campo', 'Valor'],
                    [
                        ['ID', $user->id],
                        ['Nombre', $user->name.' '.$user->last_name],
                        ['Email', $user->email],
                        ['Creado', $user->created_at],
                    ]
                );

                // 5. Probar envío de restablecimiento
                if ($this->confirm('¿Enviar correo de restablecimiento de contraseña?', true)) {
                    $this->info('5️⃣ Enviando correo de restablecimiento...');

                    try {
                        $status = Password::sendResetLink(['email' => $testEmail]);

                        if ($status === Password::RESET_LINK_SENT) {
                            $this->info('   ✅ Enlace de restablecimiento enviado exitosamente');
                            $this->line("   📧 Revisa el correo de {$testEmail}");
                            $this->warn('   ⚠️  No olvides revisar la carpeta de SPAM');
                        } else {
                            $this->error("   ❌ Error: {$status}");
                        }
                    } catch (\Exception $e) {
                        $this->error('   ❌ Excepción: '.$e->getMessage());
                    }
                }
            } else {
                $this->error("   ❌ Usuario no encontrado: {$testEmail}");

                if ($this->confirm('¿Crear usuario de prueba?', false)) {
                    try {
                        $name = $this->ask('Nombre', 'Usuario');
                        $lastName = $this->ask('Apellido', 'Prueba');
                        $password = $this->secret('Contraseña (opcional, default: password123)') ?? 'password123';

                        $user = User::create([
                            'name' => $name,
                            'last_name' => $lastName,
                            'email' => $testEmail,
                            'password' => bcrypt($password),
                        ]);

                        $this->info('   ✅ Usuario creado exitosamente');
                        $this->line("   Email: {$testEmail}");
                        $this->line("   Contraseña: {$password}");
                    } catch (\Exception $e) {
                        $this->error('   ❌ Error al crear usuario: '.$e->getMessage());
                    }
                }
            }
        }

        $this->newLine();
        $this->info('✅ Diagnóstico completado');

        return Command::SUCCESS;
    }
}
