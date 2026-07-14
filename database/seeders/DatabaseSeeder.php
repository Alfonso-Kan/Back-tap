<?php

namespace Database\Seeders;

use App\Models\Perfil;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $secciones = collect([
            ['codigo' => 'usuarios', 'nombre' => 'Usuarios'],
            ['codigo' => 'perfiles', 'nombre' => 'Perfiles'],
            ['codigo' => 'secciones', 'nombre' => 'Secciones'],
            ['codigo' => 'productos', 'nombre' => 'Productos'],
            ['codigo' => 'bitacora', 'nombre' => 'Bitácora'],
        ])->map(fn (array $datos) => Seccion::firstOrCreate(['codigo' => $datos['codigo']], $datos));

        $administrador = Perfil::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['seccion_ids' => $secciones->pluck('_id')->map(fn ($id) => (string) $id)->all()],
        );

        $seccionProductos = $secciones->firstWhere('codigo', 'productos');

        Perfil::firstOrCreate(
            ['nombre' => 'Capturista'],
            ['seccion_ids' => [(string) $seccionProductos->_id]],
        );

        if (! User::where('usuario', 'admin@tapdemo.com')->exists()) {
            User::create([
                'nombre' => 'Administrador del Sistema',
                'usuario' => 'admin@tapdemo.com',
                'password' => 'Admin123!',
                'perfil_ids' => [(string) $administrador->_id],
            ]);
        }

        $this->command?->info('Usuario admin: admin@tapdemo.com / Admin123!');
    }
}
