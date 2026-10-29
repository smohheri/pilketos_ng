<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\FaqModel;

class Faq extends BaseController
{
    public function index()
    {
        return view('admin/faqs/index', [
            'faqs' => (new FaqModel())->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->findAll(),
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function store()
    {
        if (! $this->validate([
            'question' => 'required|max_length[255]',
            'answer' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $id = (int) (new FaqModel())->insert([
            'question' => trim((string) $this->request->getPost('question')),
            'answer' => trim((string) $this->request->getPost('answer')),
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active' => $this->request->getPost('is_active') === '1' ? 1 : 0,
        ], true);
        $this->audit('faq.create', 'faq', $id);

        return redirect()->to('/admin/faq')->with('success', 'FAQ ditambahkan.');
    }

    public function update(int $id)
    {
        $faq = (new FaqModel())->find($id);
        if ($faq === null) {
            return redirect()->to('/admin/faq')->with('error', 'FAQ tidak ditemukan.');
        }
        if (! $this->validate([
            'question' => 'required|max_length[255]',
            'answer' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new FaqModel())->update($id, [
            'question' => trim((string) $this->request->getPost('question')),
            'answer' => trim((string) $this->request->getPost('answer')),
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active' => $this->request->getPost('is_active') === '1' ? 1 : 0,
        ]);
        $this->audit('faq.update', 'faq', $id);

        return redirect()->to('/admin/faq')->with('success', 'FAQ diperbarui.');
    }

    public function delete(int $id)
    {
        if ((new FaqModel())->find($id) === null) {
            return redirect()->to('/admin/faq')->with('error', 'FAQ tidak ditemukan.');
        }

        (new FaqModel())->delete($id);
        $this->audit('faq.delete', 'faq', $id);

        return redirect()->to('/admin/faq')->with('success', 'FAQ dihapus.');
    }
}