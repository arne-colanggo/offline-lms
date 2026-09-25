<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AddressController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Get all regions
     */
    public function regions()
    {
        $data = $this->db
            ->table('regions')
            ->select('region_id, region_name, region_description')
            ->orderBy('region_name', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'status' => true,
            'data' => $data
        ]);
    }


    /**
     * Get provinces by region
     */
    public function provinces($regionId)
    {
        $data = $this->db
            ->table('provinces')
            ->select('province_id, province_name')
            ->where('region_id', $regionId)
            ->orderBy('province_name', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'status' => true,
            'data' => $data
        ]);
    }


    /**
     * Get municipalities/cities by province
     */
    public function municipalities($provinceId)
    {
        $data = $this->db
            ->table('municipalities')
            ->select('municipality_id, municipality_name')
            ->where('province_id', $provinceId)
            ->orderBy('municipality_name', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'status' => true,
            'data' => $data
        ]);
    }


    /**
     * Get/search barangays
     */
    public function barangays($municipalityId)
    {
        $search = trim(
            $this->request->getGet('search') ?? ''
        );

        $builder = $this->db
            ->table('barangays')
            ->select('barangay_id, barangay_name')
            ->where('municipality_id', $municipalityId);

        if ($search !== '') {
            $builder->like(
                'barangay_name',
                $search
            );
        }

        $data = $builder
            ->orderBy('barangay_name', 'ASC')
            ->limit(100)
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'status' => true,
            'data' => $data
        ]);
    }
}
