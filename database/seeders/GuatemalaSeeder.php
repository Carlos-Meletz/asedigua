<?php

namespace Database\Seeders;

use App\Models\Locacion;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GuatemalaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Alta Verapaz' => [
                'Cobán', 'San Pedro Carchá', 'San Juan Chamelco', 'Santa Cruz Verapaz', 'Tactic',
                'Tamahú', 'Panzós', 'Senahú', 'Cahabón', 'Chisec', 'Chahal',
                'Fray Bartolomé de las Casas', 'La Tinta'
            ],
            'Baja Verapaz' => [
                'Salamá', 'Cubulco', 'Rabinal', 'Granados', 'Purulhá',
                'San Miguel Chicaj', 'San Jerónimo'
            ],
            'Chimaltenango' => [
                'Chimaltenango', 'San José Poaquil', 'San Martín Jilotepeque',
                'San Juan Comalapa', 'Santa Apolonia', 'Tecpán Guatemala', 'Patzún',
                'Pochuta', 'Patzicía', 'Santa Cruz Balanyá', 'Acatenango',
                'Yepocapa', 'San Andrés Itzapa', 'Parramos'
            ],
            'Chiquimula' => [
                'Chiquimula', 'San José La Arada', 'San Juan Ermita', 'Camotán',
                'Jocotán', 'Esquipulas', 'Concepción Las Minas', 'Quetzaltepeque', 'Olopa'
            ],
            'El Progreso' => [
                'Guastatoya', 'Morazán', 'San Agustín Acasaguastlán',
                'San Cristóbal Acasaguastlán', 'El Jícaro', 'Sansare', 'Sanarate', 'San Antonio La Paz'
            ],
            'Escuintla' => [
                'Escuintla', 'Santa Lucía Cotzumalguapa', 'La Democracia',
                'Siquinalá', 'Masagua', 'Tiquisate', 'La Gomera', 'Guanagazapa',
                'San José', 'Iztapa', 'Palín', 'Nueva Concepción'
            ],
            'Guatemala' => [
                'Guatemala', 'Santa Catarina Pinula', 'San José Pinula', 'San José del Golfo',
                'Palencia', 'Chinautla', 'San Pedro Ayampuc', 'Mixco', 'San Pedro Sacatepéquez',
                'San Juan Sacatepéquez', 'San Raymundo', 'Chuarrancho', 'Fraijanes',
                'Amatitlán', 'Villa Nueva', 'Villa Canales', 'San Miguel Petapa'
            ],
            'Huehuetenango' => [
                'Huehuetenango', 'Chiantla', 'Malacatancito', 'Cuilco', 'Nentón', 'San Pedro Necta',
                'Jacaltenango', 'Soloma', 'Ixtahuacán', 'Santa Bárbara', 'La Libertad',
                'La Democracia', 'San Miguel Acatán', 'San Rafael La Independencia', 'Todos Santos Cuchumatán',
                'San Juan Atitán', 'Santa Eulalia', 'San Mateo Ixtatán', 'Colotenango', 'San Sebastián Huehuetenango',
                'Tectitán', 'Concepción Huista', 'San Juan Ixcoy', 'San Antonio Huista', 'San Sebastián Coatán',
                'Santa Cruz Barillas', 'Aguacatán', 'San Rafael Petzal', 'San Gaspar Ixchil', 'Santiago Chimaltenango',
                'Santa Ana Huista'
            ],
            'Izabal' => [
                'Puerto Barrios', 'Livingston', 'El Estor', 'Morales', 'Los Amates'
            ],
            'Jalapa' => [
                'Jalapa', 'San Pedro Pinula', 'San Luis Jilotepeque',
                'San Manuel Chaparrón', 'San Carlos Alzatate', 'Monjas', 'Mataquescuintla'
            ],
            'Jutiapa' => [
                'Jutiapa', 'El Progreso', 'Santa Catarina Mita', 'Agua Blanca', 'Asunción Mita',
                'Yupiltepeque', 'Atescatempa', 'Jerez', 'El Adelanto', 'Zapotitlán',
                'Comapa', 'Jalpatagua', 'Conguaco', 'Moyuta', 'Pasaco', 'San José Acatempa', 'Quesada'
            ],
            'Petén' => [
                'Flores', 'San José', 'San Benito', 'San Andrés', 'La Libertad',
                'San Francisco', 'Santa Ana', 'Dolores', 'San Luis', 'Sayaxché',
                'Melchor de Mencos', 'Poptún'
            ],
            'Quetzaltenango' => [
                'Quetzaltenango', 'Salcajá', 'Olintepeque', 'San Carlos Sija',
                'Sibilia', 'Cabricán', 'Cajolá', 'San Miguel Sigüilá',
                'Ostuncalco', 'Concepción Chiquirichapa', 'San Martín Sacatepéquez',
                'Almolonga', 'Cantel', 'Huitán', 'Zunil', 'Colomba',
                'San Francisco La Unión', 'El Palmar', 'Coatepeque', 'Génova',
                'Flores Costa Cuca'
            ],
            'Quiché' => [
                'Santa Cruz del Quiché', 'Chiché', 'Chinique', 'Zacualpa', 'Chajul',
                'Chichicastenango', 'Patzité', 'San Antonio Ilotenango', 'San Pedro Jocopilas',
                'Cunén', 'San Juan Cotzal', 'Joyabaj', 'Nebaj', 'San Andrés Sajcabajá',
                'Uspantán', 'Sacapulas', 'San Bartolomé Jocotenango', 'Canillá', 'Chicamán',
                'Ixcán', 'Pachalum'
            ],
            'Retalhuleu' => [
                'Retalhuleu', 'San Sebastián', 'Santa Cruz Muluá', 'San Martín Zapotitlán',
                'San Felipe', 'San Andrés Villa Seca', 'Champerico', 'Nuevo San Carlos',
                'El Asintal'
            ],
            'Sacatepéquez' => [
                'Antigua Guatemala', 'Jocotenango', 'Pastores', 'Sumpango', 'Santo Domingo Xenacoj',
                'Santiago Sacatepéquez', 'San Bartolomé Milpas Altas', 'San Lucas Sacatepéquez',
                'Santa Lucía Milpas Altas', 'Magdalena Milpas Altas', 'Santa María de Jesús',
                'Ciudad Vieja', 'San Miguel Dueñas', 'Alotenango', 'San Antonio Aguas Calientes'
            ],
            'San Marcos' => [
                'San Marcos', 'San Pedro Sacatepéquez', 'San Antonio Sacatepéquez', 'Comitancillo',
                'San Miguel Ixtahuacán', 'Concepción Tutuapa', 'Tacaná', 'Sibinal', 'Tajumulco',
                'Tejutla', 'San Rafael Pie de la Cuesta', 'Nuevo Progreso', 'El Tumbador',
                'El Rodeo', 'Malacatán', 'Catarina', 'Ayutla', 'Ocós',
                'San Pablo', 'Ixchiguán', 'San José Ojetenam', 'San Cristóbal Cucho',
                'Sipacapa', 'Esquipulas Palo Gordo', 'Río Blanco', 'San Lorenzo'
            ],
            'Santa Rosa' => [
                'Cuilapa', 'Barberena', 'Santa Rosa de Lima', 'Casillas', 'San Rafael Las Flores',
                'Oratorio', 'San Juan Tecuaco', 'Chiquimulilla', 'Taxisco', 'Santa María Ixhuatán',
                'Guazacapán', 'Santa Cruz Naranjo', 'Pueblo Nuevo Viñas', 'Nueva Santa Rosa'
            ],
            'Sololá' => [
                'Sololá', 'San José Chacayá', 'Santa María Visitación', 'Santa Lucía Utatlán',
                'Nahualá', 'Santa Catarina Ixtahuacán', 'Santa Clara La Laguna', 'Concepción',
                'San Andrés Semetabaj', 'Panajachel', 'San Antonio Palopó', 'Santa Catarina Palopó',
                'San Lucas Tolimán', 'Santa Cruz La Laguna', 'San Juan La Laguna',
                'San Pablo La Laguna', 'San Marcos La Laguna', 'San Pedro La Laguna',
                'Santiago Atitlán'
            ],
            'Suchitepéquez' => [
                'Mazatenango', 'Cuyotenango', 'San Francisco Zapotitlán', 'San Bernardino',
                'San José El Idolo', 'Santo Domingo Suchitepéquez', 'San Lorenzo',
                'Samayac', 'San Pablo Jocopilas', 'San Antonio Suchitepéquez',
                'San Miguel Panán', 'San Gabriel', 'Chicacao', 'Patulul',
                'Santa Bárbara', 'Pueblo Nuevo', 'Río Bravo', 'Zunilito'
            ],
            'Totonicapán' => [
                'Totonicapán', 'San Cristóbal Totonicapán', 'San Francisco El Alto',
                'San Andrés Xecul', 'Momostenango', 'Santa María Chiquimula',
                'Santa Lucía La Reforma', 'San Bartolo Aguas Calientes'
            ],
            'Zacapa' => [
                'Zacapa', 'Estanzuela', 'Río Hondo', 'Gualán', 'Teculután',
                'Usumatlán', 'Cabañas', 'San Diego', 'La Unión', 'Huité'
            ]
        ];

        foreach ($departments as $department => $municipalities) {
            $departmentRecord = Locacion::create([
                'type' => 'Departamento',
                'name' => $department,
                'parent_id' => null,
            ]);

            foreach ($municipalities as $municipality) {
                Locacion::create([
                    'type' => 'Municipio',
                    'name' => $municipality,
                    'parent_id' => $departmentRecord->id,
                ]);
            }
    }
    }
}
