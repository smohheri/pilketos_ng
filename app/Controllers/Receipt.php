<?php

namespace App\Controllers;

use App\Libraries\ElectionService;
use App\Libraries\VotingService;
use App\Models\SchoolProfileModel;

class Receipt extends BaseController
{
    public function verify()
    {
        $result = null;
        $receipt = '';

        if ($this->request->is('post')) {
            $receipt = strtoupper(trim((string) $this->request->getPost('receipt_code')));
            if (! preg_match('/^CK26-[A-Z2-9]{4}-[A-Z2-9]{4}$/', $receipt)) {
                $result = ['valid' => false, 'message' => 'Format kode bukti tidak sesuai. Contoh: CK26-ABCD-2345.'];
            } else {
                $verified = (new VotingService())->verifyReceipt($receipt);
                $result = $verified !== null
                    ? ['valid' => true, 'cast_at' => $verified['cast_at']]
                    : ['valid' => false, 'message' => 'Kode bukti tidak ditemukan.'];
            }
        }

        return view('landing/receipt', [
            'profile' => (new SchoolProfileModel())->active(),
            'election' => (new ElectionService())->current(),
            'receipt' => $receipt,
            'result' => $result,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }
}