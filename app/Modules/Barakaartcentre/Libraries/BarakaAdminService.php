<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Libraries;

use App\Modules\Barakaartcentre\Models\ArtworkModel;
use App\Modules\Barakaartcentre\Models\ServiceModel;
use App\Modules\Barakaartcentre\Models\WorkshopModel;
use App\Modules\Barakaartcentre\Models\OrderModel;
use App\Modules\Barakaartcentre\Models\SignupModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Model;
use Config\Database;

/**
 * Class BarakaAdminService
 * Handles all business logic and CRUD operations for the Admin panel.
 */
class BarakaAdminService
{
    protected ArtworkModel $artworkModel;
    protected ServiceModel $serviceModel;
    protected WorkshopModel $workshopModel;
    protected SignupModel $signupModel;
    protected OrderModel $orderModel;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->artworkModel = new ArtworkModel();
        $this->serviceModel = new ServiceModel();
        $this->workshopModel = new WorkshopModel();
        $this->signupModel = new SignupModel();
        $this->orderModel = new OrderModel();
        $this->db = Database::connect();
    }

    // --- Helper Methods ---

    /**
     * Internal unified save logic for all entity types.
     * Handles Transactions, File Uploads, and File Cleanups.
     */
    private function _processSave(Model $model, array $data, ?int $id, ?UploadedFile $file, string $imgField): bool
    {
        $this->db->transStart();
        try {
            /** @var \CodeIgniter\Entity\Entity|null $oldRecord */
            $oldRecord = $id ? $model->find($id) : null;

            // Handle new file upload
            if ($uploadedUrl = $this->_handleImageUpload($file)) {
                $data[$imgField] = $uploadedUrl;
            }

            if ($id) {
                // UPDATE: If image changed, delete the old file
                if ($oldRecord && isset($data[$imgField]) && $data[$imgField] !== $oldRecord->{$imgField}) {
                    $this->_deleteFileIfUploaded($oldRecord->{$imgField});
                }
                $model->update($id, $data);
            } else {
                // INSERT
                $model->insert($data, false);
            }

            $this->db->transComplete();
            return $this->db->transStatus() !== false;
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', '[BarakaAdminService] Save failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    /**
     * Handles image upload and returns the public URL.
     */
    private function _handleImageUpload(?UploadedFile $file): ?string
    {
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $relativePath = 'uploads/baraka/' . date('Y/m');
            
            $absoluteDir = FCPATH . $relativePath;
            if (!is_dir($absoluteDir)) {
                mkdir($absoluteDir, 0777, true);
            }
            
            $file->move($absoluteDir, $newName);
            return base_url($relativePath . '/' . $newName);
        }
        return null;
    }

    /**
     * Physically deletes a file if it belongs to our local uploads.
     */
    private function _deleteFileIfUploaded(?string $path): void
    {
        if (!$path) return;

        $uploadBase = base_url('uploads/baraka/');
        if (strpos($path, $uploadBase) === 0) {
            $relativePath = str_replace(base_url(), '', $path);
            $absolutePath = FCPATH . ltrim($relativePath, '/');
            if (file_exists($absolutePath) && is_file($absolutePath)) {
                @unlink($absolutePath);
            }
        }
    }

    // --- Public Orchestration ---

    /**
     * Retrieves dashboard statistics.
     */
    public function getDashboardStats(): array
    {
        return [
            'total_artworks'   => $this->artworkModel->countAllResults(),
            'total_services'   => $this->serviceModel->countAllResults(),
            'total_workshops'  => $this->workshopModel->countAllResults(),
            'total_signups'    => $this->signupModel->countAllResults(),
            'recent_signups'   => $this->signupModel->orderBy('created_at', 'desc')->findAll(5),
            'recent_workshops' => $this->workshopModel->orderBy('event_date', 'asc')->findAll(5),
        ];
    }

    // --- ARTWORKS ----------------------------------------------------------

    public function getAllArtworks(): array
    {
        return $this->artworkModel->orderBy('created_at', 'DESC')->findAll();
    }

    public function getArtworkById(int $id)
    {
        return $this->artworkModel->find($id);
    }

    public function saveArtwork(array $data, ?int $id = null, ?UploadedFile $file = null): bool
    {
        return $this->_processSave($this->artworkModel, $data, $id, $file, 'image_path');
    }

    public function deleteArtwork(int $id): bool
    {
        $this->db->transStart();
        $record = $this->artworkModel->find($id);
        if ($record && $this->artworkModel->delete($id)) {
            $this->_deleteFileIfUploaded($record->image_path);
            $this->db->transComplete();
            return $this->db->transStatus();
        }
        $this->db->transRollback();
        return false;
    }
    
    // --- SERVICES ----------------------------------------------------------

    public function getAllServices(): array
    {
        return $this->serviceModel->orderBy('created_at', 'DESC')->findAll();
    }

    public function getServiceById(int $id)
    {
        return $this->serviceModel->find($id);
    }

    public function saveService(array $data, ?int $id = null, ?UploadedFile $file = null): bool
    {
        return $this->_processSave($this->serviceModel, $data, $id, $file, 'icon_or_image');
    }

    public function deleteService(int $id): bool
    {
        $this->db->transStart();
        $record = $this->serviceModel->find($id);
        if ($record && $this->serviceModel->delete($id)) {
            $this->_deleteFileIfUploaded($record->icon_or_image);
            $this->db->transComplete();
            return $this->db->transStatus();
        }
        $this->db->transRollback();
        return false;
    }

    // --- WORKSHOPS ---------------------------------------------------------

    public function getAllWorkshops(): array
    {
        return $this->workshopModel->orderBy('event_date', 'DESC')->findAll();
    }

    public function getWorkshopById(int $id)
    {
        return $this->workshopModel->find($id);
    }

    public function saveWorkshop(array $data, ?int $id = null, ?UploadedFile $file = null): bool
    {
        return $this->_processSave($this->workshopModel, $data, $id, $file, 'image_path');
    }

    public function deleteWorkshop(int $id): bool
    {
        $this->db->transStart();
        $record = $this->workshopModel->find($id);
        if ($record && $this->workshopModel->delete($id)) {
            $this->_deleteFileIfUploaded($record->image_path);
            $this->db->transComplete();
            return $this->db->transStatus();
        }
        $this->db->transRollback();
        return false;
    }

    // --- SIGNUPS -----------------------------------------------------------

    public function getAllSignups(): array
    {
        return $this->signupModel->orderBy('created_at', 'DESC')->findAll();
    }

    public function deleteSignup(int $id): bool
    {
        return $this->signupModel->delete($id);
    }

    // --- PAYMENTS & ORDERS -------------------------------------------------

    /**
     * Retrieves all orders for the admin dashboard.
     * 
     * @param string|null $status Filter by status.
     * @return array
     */
    public function getAllOrders(?string $status = null): array
    {
        if ($status) {
            $this->orderModel->where('status', $status);
        }
        $orders = $this->orderModel->orderBy('created_at', 'DESC')->findAll();

        // Map titles for visibility
        if (!empty($orders)) {
            $artworkIds = [];
            $workshopIds = [];
            foreach ($orders as $order) {
                if ($order->item_type === 'artwork') $artworkIds[] = $order->item_id;
                if ($order->item_type === 'workshop') $workshopIds[] = $order->item_id;
            }

            $artworkTitles = !empty($artworkIds) ? $this->artworkModel->whereIn('id', array_unique($artworkIds))->select('id, title')->findAll() : [];
            $workshopTitles = !empty($workshopIds) ? $this->workshopModel->whereIn('id', array_unique($workshopIds))->select('id, title')->findAll() : [];

            $artworkMap = [];
            foreach ($artworkTitles as $a) $artworkMap[$a->id] = $a->title;
            $workshopMap = [];
            foreach ($workshopTitles as $w) $workshopMap[$w->id] = $w->title;

            foreach ($orders as $order) {
                if ($order->item_type === 'artwork') {
                    $order->item_title = $artworkMap[$order->item_id] ?? 'Deleted Artwork (ID: ' . $order->item_id . ')';
                } else {
                    $order->item_title = $workshopMap[$order->item_id] ?? 'Deleted Workshop (ID: ' . $order->item_id . ')';
                }
            }
        }

        return $orders;
    }

    /**
     * Checks if an item has any successful orders.
     * Use this to block deletion of revenue-linked items.
     */
    public function hasUnresolvedOrders(string $type, int $id): bool
    {
        return $this->orderModel->where('item_type', $type)
                                ->where('item_id', $id)
                                ->where('status', 'success')
                                ->where('is_resolved', 0)
                                ->countAllResults() > 0;
    }

    /**
     * Marks an order as resolved/fulfilled.
     */
    public function resolveOrder(int $id): bool
    {
        return $this->orderModel->update($id, ['is_resolved' => 1]);
    }
}


