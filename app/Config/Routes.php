<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Landing::index');
$routes->get('kandidat/(:num)', 'Landing::candidate/$1');
$routes->get('hasil', 'Landing::results');
$routes->get('hasil/(:segment)', 'Landing::results/$1');
$routes->get('qr.svg', 'Landing::qr');
$routes->match(['GET', 'POST'], 'bukti', 'Receipt::verify');

$routes->get('aktivasi', 'Auth::activation');
$routes->post('aktivasi', 'Auth::sendActivation');
$routes->match(['GET', 'POST'], 'aktivasi/otp', 'Auth::activationOtp');
$routes->get('masuk', 'Auth::login');
$routes->post('masuk', 'Auth::sendLogin');
$routes->match(['GET', 'POST'], 'masuk/otp', 'Auth::loginOtp');
$routes->post('keluar', 'Auth::logout');

$routes->group('voting', ['filter' => 'studentauth'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Vote::index');
    $routes->post('konfirmasi', 'Vote::confirm');
    $routes->post('kirim', 'Vote::submit');
    $routes->get('selesai', 'Vote::done');
});

$routes->get('akun', 'Account::index', ['filter' => 'studentauth']);

$routes->group('admin', static function (RouteCollection $routes): void {
    $routes->get('login', 'Admin\Auth::login');
    $routes->post('login', 'Admin\Auth::attempt');
    $routes->post('logout', 'Admin\Auth::logout');

    $routes->group('', ['filter' => 'adminauth'], static function (RouteCollection $routes): void {
        $routes->get('/', 'Admin\Dashboard::index');

        $routes->get('profil', 'Admin\Profile::index');
        $routes->post('profil', 'Admin\Profile::update');

        $routes->get('kandidat', 'Admin\Candidate::index');
        $routes->get('kandidat/baru', 'Admin\Candidate::create');
        $routes->post('kandidat', 'Admin\Candidate::store');
        $routes->get('kandidat/(:num)/ubah', 'Admin\Candidate::edit/$1');
        $routes->post('kandidat/(:num)', 'Admin\Candidate::update/$1');
        $routes->post('kandidat/(:num)/status', 'Admin\Candidate::toggle/$1');
        $routes->post('kandidat/(:num)/hapus', 'Admin\Candidate::delete/$1');

        $routes->get('pemilihan', 'Admin\Election::index');
        $routes->post('pemilihan', 'Admin\Election::update');
        $routes->post('pemilihan/pilih/(:num)', 'Admin\Election::select/$1');
        $routes->post('pemilihan/sinkron', 'Admin\Election::syncVoters');
        $routes->post('pemilihan/jadwal', 'Admin\Election::storeSchedule');
        $routes->post('pemilihan/jadwal/(:num)', 'Admin\Election::updateSchedule/$1');
        $routes->post('pemilihan/jadwal/(:num)/hapus', 'Admin\Election::deleteSchedule/$1');

        $routes->get('penilaian', 'Admin\Assessment::index');
        $routes->post('penilaian/nilai', 'Admin\Assessment::saveScores');
        $routes->post('penilaian/kriteria', 'Admin\Assessment::storeCriterion');
        $routes->post('penilaian/kriteria/(:num)', 'Admin\Assessment::updateCriterion/$1');
        $routes->post('penilaian/kriteria/(:num)/status', 'Admin\Assessment::toggleCriterion/$1');
        $routes->post('penilaian/kriteria/(:num)/hapus', 'Admin\Assessment::deleteCriterion/$1');

        $routes->get('siswa', 'Admin\Student::index');
        $routes->get('siswa/(:num)/ubah', 'Admin\Student::edit/$1');
        $routes->post('siswa/(:num)', 'Admin\Student::update/$1');
        $routes->post('siswa/(:num)/status', 'Admin\Student::toggle/$1');
        $routes->get('siswa/import', 'Admin\ImportStudent::index');
        $routes->post('siswa/import/preview', 'Admin\ImportStudent::preview');
        $routes->post('siswa/import/konfirmasi', 'Admin\ImportStudent::confirm');
        $routes->get('siswa/import/template', 'Admin\ImportStudent::template');
        $routes->get('siswa/export', 'Admin\Student::export');

        $routes->get('laporan', 'Admin\Report::index');
        $routes->get('laporan/export', 'Admin\Report::export');
        $routes->get('laporan/pdf', 'Admin\Report::pdf');

        $routes->get('faq', 'Admin\Faq::index');
        $routes->post('faq', 'Admin\Faq::store');
        $routes->post('faq/(:num)', 'Admin\Faq::update/$1');
        $routes->post('faq/(:num)/hapus', 'Admin\Faq::delete/$1');

        $routes->group('', ['filter' => 'roleadmin'], static function (RouteCollection $routes): void {
            $routes->get('pengguna', 'Admin\User::index');
            $routes->post('pengguna', 'Admin\User::store');
            $routes->post('pengguna/(:num)', 'Admin\User::update/$1');
            $routes->post('pengguna/(:num)/status', 'Admin\User::toggle/$1');

            $routes->get('audit', 'Admin\Audit::index');
            $routes->get('mode-aplikasi', 'Admin\ElectionMode::index');
            $routes->post('mode-aplikasi', 'Admin\ElectionMode::update');
            $routes->get('konten', 'Admin\Content::index');
            $routes->post('konten', 'Admin\Content::update');
            $routes->get('pengaturan', 'Admin\Setting::index');
            $routes->post('pengaturan', 'Admin\Setting::update');
            $routes->get('pengaturan/openwa', 'Admin\Setting::openwa');
            $routes->post('pengaturan/openwa', 'Admin\Setting::updateOpenwa');
            $routes->post('pengaturan/openwa/test', 'Admin\Setting::testOpenwa');
        });
    });
});

$routes->get('admin/media/qr', 'Landing::qr');