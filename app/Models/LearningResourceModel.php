<?php

namespace App\Models;

use CodeIgniter\Model;

class LearningResourceModel extends Model
{
    // NOTE: table name is spelled 'learningresouces' in the migration. Keep in sync with it.
    protected $table = 'learningresources';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields = ['bowdetailsid', 'resourcestype', 'filetype', 'filename', 'visibletostudents', 'documentname'];
    protected $useTimestamps = false;

    /** Resources of many details, grouped as [bowdetailsid => [resource, ...]] */
    public function groupedByDetail(array $detailIds): array
    {
        if (empty($detailIds)) {
            return [];
        }
        $out = [];
        foreach ($this->whereIn('bowdetailsid', $detailIds)->orderBy('id')->findAll() as $r) {
            $out[$r->bowdetailsid][] = $r;
        }
        return $out;
    }
}
