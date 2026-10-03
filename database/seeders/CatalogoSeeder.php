<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->catalogo() as $nombre => $datos) {
            // firstOrCreate y updateOrCreate permiten volver a ejecutar el seeder sin duplicar registros
            $categoria = Categoria::firstOrCreate(['nombre' => $nombre], ['descripcion' => $datos['descripcion']]);

            foreach ($datos['productos'] as [$titulo, $descripcion, $precio, $estatus]) {
                Producto::updateOrCreate(
                    ['categoria_id' => $categoria->id, 'titulo' => $titulo],
                    ['descripcion' => $descripcion, 'precio' => $precio, 'estatus' => $estatus],
                );
            }
        }
    }

    /**
     * Cada producto: [título, descripción, precio en MXN, estatus].
     */
    private function catalogo(): array
    {
        $activo = Producto::ESTATUS_ACTIVO;
        $inactivo = Producto::ESTATUS_INACTIVO;

        return [
            'Electrónica' => [
                'descripcion' => 'Dispositivos, gadgets y accesorios electrónicos.',
                'productos' => [
                    ['Laptop Lenovo IdeaPad Slim 3 15"', 'Ryzen 5, 16 GB de RAM y SSD de 512 GB.', 12499.00, $activo],
                    ['Smartphone Samsung Galaxy A55 5G', 'Pantalla AMOLED de 6.6", 256 GB, cámara de 50 MP.', 7999.00, $activo],
                    ['Audífonos Sony WH-CH720N', 'Inalámbricos con cancelación de ruido y 35 horas de batería.', 2299.00, $activo],
                    ['Pantalla LG 55" 4K UHD Smart TV', 'Panel LED con webOS y HDR10.', 8999.00, $activo],
                    ['Tablet Apple iPad 10.9" 64 GB', 'Décima generación, Wi-Fi, chip A14 Bionic.', 7499.00, $activo],
                    ['Mouse Logitech MX Master 3S', 'Inalámbrico, sensor de 8000 DPI y clics silenciosos.', 1899.00, $activo],
                    ['Bocina JBL Flip 6', 'Bluetooth portátil, resistente al agua (IP67).', 2199.00, $activo],
                    ['Consola Nintendo Switch OLED', 'Pantalla OLED de 7" y 64 GB de almacenamiento.', 6999.00, $inactivo],
                ],
            ],
            'Hogar' => [
                'descripcion' => 'Muebles, decoración y artículos para el hogar.',
                'productos' => [
                    ['Licuadora Oster Xpert Series', 'Motor de 2 HP, vaso de Tritan de 2 litros.', 2499.00, $activo],
                    ['Aspiradora Koblenz Inalámbrica', 'Tipo escoba, 2 en 1, batería de 40 minutos.', 1799.00, $activo],
                    ['Cafetera Nespresso Vertuo Pop', 'Cápsulas, calentamiento en 30 segundos.', 2299.00, $activo],
                    ['Freidora de aire Ninja 5.2 L', 'Cuatro funciones y canasta antiadherente.', 2799.00, $activo],
                    ['Juego de sábanas Vianney matrimonial', 'Microfibra, cuatro piezas.', 549.00, $activo],
                    ['Sartén T-fal Easy Care 28 cm', 'Antiadherente con indicador de temperatura.', 399.00, $activo],
                    ['Horno de microondas Mabe 1.1 pies', 'Diez niveles de potencia y descongelado automático.', 2199.00, $activo],
                    ['Ventilador de torre Taurus', 'Tres velocidades, oscilación y control remoto.', 1299.00, $inactivo],
                ],
            ],
            'Oficina' => [
                'descripcion' => 'Papelería, mobiliario y equipo de oficina.',
                'productos' => [
                    ['Silla ergonómica Ofik Mesh', 'Respaldo de malla, soporte lumbar y descansabrazos ajustables.', 3299.00, $activo],
                    ['Escritorio en L Techni Mobili', 'Cubierta de melamina con repisa para CPU.', 2899.00, $activo],
                    ['Impresora multifuncional HP Smart Tank 580', 'Tinta continua, Wi-Fi, imprime, copia y escanea.', 3999.00, $activo],
                    ['Monitor Dell 24" Full HD', 'Panel IPS de 75 Hz con HDMI y VGA.', 2599.00, $activo],
                    ['Teclado mecánico Logitech K845', 'Retroiluminado, switches TTC rojos, distribución en español.', 1299.00, $activo],
                    ['Paquete de papel bond Scribe carta', 'Caja con 5000 hojas de 75 g/m².', 1099.00, $activo],
                    ['Archivero metálico de 3 gavetas', 'Con cerradura, tamaño oficio.', 2499.00, $inactivo],
                    ['Engrapadora Pilot de uso rudo', 'Capacidad de hasta 100 hojas.', 459.00, $activo],
                ],
            ],
            'Deportes' => [
                'descripcion' => 'Equipo y ropa para actividades deportivas.',
                'productos' => [
                    ['Tenis Nike Revolution 7', 'Para correr, suela de espuma y malla transpirable.', 1399.00, $activo],
                    ['Balón de fútbol Adidas Tiro League', 'Número 5, cosido a máquina.', 499.00, $activo],
                    ['Bicicleta de montaña Benotto rodada 29', 'Cuadro de aluminio, 21 velocidades y frenos de disco.', 6499.00, $activo],
                    ['Mancuernas ajustables de 20 kg', 'Par con discos intercambiables y maletín.', 1199.00, $activo],
                    ['Tapete de yoga Manduka 6 mm', 'Antiderrapante, 180 x 61 cm.', 899.00, $activo],
                    ['Reloj Garmin Forerunner 55', 'GPS, monitor de ritmo cardiaco y planes de entrenamiento.', 3999.00, $activo],
                    ['Raqueta de pádel Bullpadel Indiga', 'Forma redonda, núcleo de goma EVA.', 1899.00, $inactivo],
                    ['Botella térmica Stanley 1 L', 'Acero inoxidable, conserva la temperatura 24 horas.', 899.00, $activo],
                ],
            ],
            'Juguetes' => [
                'descripcion' => 'Juguetes y juegos de mesa para todas las edades.',
                'productos' => [
                    ['LEGO Classic Caja de ladrillos creativos', 'Set de 790 piezas en 33 colores.', 999.00, $activo],
                    ['Monopoly Clásico', 'Juego de mesa para 2 a 6 jugadores.', 449.00, $activo],
                    ['Hot Wheels paquete de 10 autos', 'Autos a escala 1:64, modelos surtidos.', 349.00, $activo],
                    ['Barbie Casa de los Sueños', 'Tres pisos, resbaladilla y más de 75 accesorios.', 4299.00, $activo],
                    ['Lanzador Nerf Elite 2.0 Commander', 'Tambor giratorio para seis dardos.', 399.00, $activo],
                    ['UNO', 'Juego de cartas para toda la familia.', 129.00, $activo],
                    ['Rompecabezas Ravensburger 1000 piezas', 'Paisaje, 70 x 50 cm.', 459.00, $activo],
                    ['Play-Doh paquete de 12 botes', 'Masa moldeable en colores surtidos.', 299.00, $inactivo],
                ],
            ],
        ];
    }
}
