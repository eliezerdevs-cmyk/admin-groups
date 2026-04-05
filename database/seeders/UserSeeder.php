<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $disk = Storage::disk('private');
        $directory = 'users/photos';
        
        // Asegurar que el directorio existe
        if (! $disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        // 1. Crear el Super Usuario
        $superAdmin = User::factory()->create([
            'name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'photo' => $this->downloadRandomAvatar($disk, $directory),
        ]);
        
        $superAdmin->assignRole('super_admin');
        
        // Opcional: Asegurar que el rol super_admin tenga todos los permisos (si existen)
        $role = Role::where('name', 'super_admin')->first();
        if ($role) {
            $role->syncPermissions(Permission::all());
        }

        // 2. Crear 9 usuarios adicionales con rol registrado y fotos
        User::factory(9)->create()->each(function ($user) use ($disk, $directory) {
            $user->update(['photo' => $this->downloadRandomAvatar($disk, $directory)]);
            $user->assignRole('registrado');
        });
    }

    private function downloadRandomAvatar($disk, $directory): ?string
    {
        try {
            $response = Http::get('https://i.pravatar.cc/300');
            if ($response->successful()) {
                $filename = $directory . '/' . uniqid() . '.jpg';
                $disk->put($filename, $response->body());
                return $filename;
            }
        } catch (\Exception $e) {
            // Si falla la descarga, ignorar silenciosamente
        }
        return null;
    }
}
