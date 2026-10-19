<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\BOWModel;
use App\Models\BOWDetailsModel;
use App\Models\LearningResourceModel;

class BOWController extends BaseController
{
    protected BOWModel $bows;
    protected BOWDetailsModel $details;
    protected LearningResourceModel $resources;
    protected string $uploadDir;
    protected $helpers = ['url', 'form', 'CIMail', 'CIFunctions'];
    public function __construct()
    {
        $this->bows = new BowModel();
        $this->details = new BOWDetailsModel();
        $this->resources = new LearningResourceModel();
        $this->uploadDir = WRITEPATH . 'uploads/bow/'; // outside the web root
    }

    /* ------------------------------------------------------------------ */
    /* Pages                                                              */
    /* ------------------------------------------------------------------ */

    public function index()
    {
        $subjects = db_connect()->table('subjects')
            ->select('subjects.id, subjects.name, gradelevels.name AS grade_name')
            ->join('gradelevels', 'gradelevels.id = subjects.grade_level_id', 'left')
            ->orderBy('gradelevels.id')->orderBy('subjects.name')
            ->get()->getResult();

        return view('backend/pages/bow/index', [
            'pageTitle' => 'Budget of Work',
            'bows' => $this->bows->listWithSubject(),
            'subjects' => $subjects,
            'canManage' => $this->canManage(),
        ]);
    }

    public function view()
    {
        $id = (int) $this->request->getGet('id');
        $bow = $this->bows->findWithSubject($id);
        if (!$bow) {
            throw PageNotFoundException::forPageNotFound('Budget of Work not found.');
        }

        $details = $this->details->forBow($bow->id);
        $ids = array_map(fn($d) => $d->id, $details);

        return view('backend/pages/bow/view', [
            'pageTitle' => 'BOW - ' . $bow->name,
            'bow' => $bow,
            'details' => $details,
            'resources' => $this->resources->groupedByDetail($ids),
            'canManage' => $this->canManage(),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* BOW (header) CRUD                                                  */
    /* ------------------------------------------------------------------ */

    public function save()
    {
        if (!$this->canManage())
            return $this->deny();

        $rules = [
            'subjectid' => 'required|is_natural_no_zero',
            'term' => 'required|in_list[1,2,3,4]',
            'name' => 'required|min_length[3]|max_length[150]',
        ];
        if (!$this->validate($rules)) {
            return $this->json(0, 'Please fix the errors.', $this->validator->getErrors());
        }

        $data = [
            'subjectid' => (int) $this->request->getPost('subjectid'),
            'term' => (int) $this->request->getPost('term'),
            'name' => trim($this->request->getPost('name')),
        ];

        // one BOW per subject + term
        $id = (int) $this->request->getPost('id');
        $dup = $this->bows
            ->where('subjectid', $data['subjectid'])
            ->where('term', $data['term'])
            ->where('id !=', $id)
            ->first();
        if ($dup) {
            return $this->json(0, 'A BOW for this subject and quarter already exists.', ['term' => 'Already exists for this subject.']);
        }

        if ($id > 0) {
            $this->bows->update($id, $data);
            return $this->json(1, 'BOW updated.');
        }
        $this->bows->insert($data);
        return $this->json(1, 'BOW created.');
    }

    public function delete()
    {
        if (!$this->canManage())
            return $this->deny();

        $id = (int) $this->request->getPost('id');
        if (!$this->bows->find($id)) {
            return $this->json(0, 'BOW not found.');
        }

        // cascade manually (the migration defines no foreign keys)
        foreach ($this->details->where('bowid', $id)->findAll() as $d) {
            $this->removeDetail((int) $d->id);
        }
        $this->bows->delete($id);

        return $this->json(1, 'BOW deleted.');
    }

    /* ------------------------------------------------------------------ */
    /* Competency / weekly details                                        */
    /* ------------------------------------------------------------------ */

    public function saveDetail()
    {
        if (!$this->canManage())
            return $this->deny();

        $rules = [
            'bowid' => 'required|is_natural_no_zero',
            'week' => 'required|is_natural_no_zero|less_than_equal_to[52]',
            'competencycode' => 'required|max_length[50]',
            'learning_objectives' => 'required',
        ];
        if (!$this->validate($rules)) {
            return $this->json(0, 'Please fix the errors.', $this->validator->getErrors());
        }

        $bowId = (int) $this->request->getPost('bowid');
        if (!$this->bows->find($bowId)) {
            return $this->json(0, 'BOW not found.');
        }

        $data = [
            'bowid' => $bowId,
            'week' => (int) $this->request->getPost('week'),
            'competencycode' => trim($this->request->getPost('competencycode')),
            'learning_objectives' => trim($this->request->getPost('learning_objectives')),
        ];

        $id = (int) $this->request->getPost('id');
        if ($id > 0) {
            $this->details->update($id, $data);
            return $this->json(1, 'Competency updated.');
        }
        $this->details->insert($data);
        return $this->json(1, 'Competency added.');
    }

    public function deleteDetail()
    {
        if (!$this->canManage())
            return $this->deny();

        $id = (int) $this->request->getPost('id');
        if (!$this->details->find($id)) {
            return $this->json(0, 'Competency not found.');
        }
        $this->removeDetail($id);
        return $this->json(1, 'Competency deleted.');
    }

    /* ------------------------------------------------------------------ */
    /* Learning resources (files)                                         */
    /* ------------------------------------------------------------------ */

    public function uploadResource()
    {
        if (!$this->canManage())
            return $this->deny();

        $rules = [
            'bowdetailsid' => 'required|is_natural_no_zero',
            'resourcestype' => 'required|in_list[LP,LR]',
            'resource_file' => 'uploaded[resource_file]|max_size[resource_file,10240]'
                . '|ext_in[resource_file,pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,mp4]',
            'documentname' => 'required'
        ];
        if (!$this->validate($rules)) {
            return $this->json(0, 'Please fix the errors.', $this->validator->getErrors());
        }

        $detailId = (int) $this->request->getPost('bowdetailsid');
        if (!$this->details->find($detailId)) {
            return $this->json(0, 'Competency not found.');
        }

        $file = $this->request->getFile('resource_file');
        if (!$file->isValid() || $file->hasMoved()) {
            return $this->json(0, 'Invalid upload.');
        }

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }

        $stored = $file->getRandomName();   // fits the varchar(50) column
        $file->move($this->uploadDir, $stored);

        $this->resources->insert([
            'bowdetailsid' => $detailId,
            'resourcestype' => $this->request->getPost('resourcestype'),
            'filetype' => strtolower($file->getClientExtension()),
            'filename' => $stored,
            'documentname' => $this->request->getPost('documentname'),
            'visibletostudents' => ($this->request->getPost('resourcestype')) === 'LR' ? 'T' : 'F'
        ]);

        return $this->json(1, 'Resource uploaded.');
    }

    public function deleteResource()
    {
        if (!$this->canManage())
            return $this->deny();

        $res = $this->resources->find((int) $this->request->getPost('id'));
        if (!$res) {
            return $this->json(0, 'Resource not found.');
        }
        $this->unlinkFile($res->filename);
        $this->resources->delete($res->id);
        return $this->json(1, 'Resource deleted.');
    }

    public function download()
    {
        $res = $this->resources->find((int) $this->request->getGet('id'));
        $path = $res ? $this->uploadDir . basename($res->filename) : '';
        if (!$res || !is_file($path)) {
            throw PageNotFoundException::forPageNotFound('File not found.');
        }
        return $this->response->download($path, null);
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                            */
    /* ------------------------------------------------------------------ */

    private function removeDetail(int $detailId): void
    {
        foreach ($this->resources->where('bowdetailsid', $detailId)->findAll() as $r) {
            $this->unlinkFile($r->filename);
        }
        $this->resources->where('bowdetailsid', $detailId)->delete();
        $this->details->delete($detailId);
    }

    private function unlinkFile(string $name): void
    {
        $path = $this->uploadDir . basename($name);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /** admin + teacher can edit; students are read-only */
    private function canManage(): bool
    {
        $user = session()->get('userdata');
        $role = is_array($user) ? ($user['role'] ?? '') : ($user->role ?? '');
        return in_array($role, ['admin', 'teacher'], true);
    }

    private function deny()
    {
        return $this->json(0, 'You are not allowed to do this.');
    }

    /** Same JSON shape your other pages expect: status, msg, error, token */
    private function json(int $status, string $msg, array $error = [])
    {
        return $this->response->setJSON([
            'status' => $status,
            'msg' => $msg,
            'error' => $error,
            'token' => csrf_hash(),
        ]);
    }
}
