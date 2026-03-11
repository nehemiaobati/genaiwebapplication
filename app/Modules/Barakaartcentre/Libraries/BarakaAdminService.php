<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Libraries;

use App\Modules\Barakaartcentre\Models\ArtworkModel;
use App\Modules\Barakaartcentre\Models\ServiceModel;
use App\Modules\Barakaartcentre\Models\WorkshopModel;
use App\Modules\Barakaartcentre\Models\SignupModel;
use CodeIgniter\HTTP\Files\UploadedFile;

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

    public function __construct()
    {
        $this->artworkModel = new ArtworkModel();
        $this->serviceModel = new ServiceModel();
        $this->workshopModel = new WorkshopModel();
        $this->signupModel = new SignupModel();
    }

    /**
     * Retrieves dashboard statistics.
     * @return array
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

    // --- FILE UPLOADS ------------------------------------------------------

    /**
     * Handles image upload and returns the public URL.
     */
    private function handleImageUpload(?UploadedFile $file): ?string
    {
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $relativePath = 'uploads/baraka/' . date('Y/m');
            // Ensure directory exists
            if (!is_dir(FCPATH . $relativePath)) {
                mkdir(FCPATH . $relativePath, 0777, true);
            }
            $file->move(FCPATH . $relativePath, $newName);
            return base_url($relativePath . '/' . $newName);
        }
        return null;
    }

    /**
     * Physically deletes a file if it belongs to our local uploads.
     */
    private function deleteFileIfUploaded(?string $path): void
    {
        if (!$path) return;

        $uploadBase = base_url('uploads/baraka/');
        if (strpos($path, $uploadBase) === 0) {
            $relativePath = str_replace(base_url(), '', $path);
            $absolutePath = FCPATH . ltrim($relativePath, '/');
            if (file_exists($absolutePath) && is_file($absolutePath)) {
                unlink($absolutePath);
            }
        }
    }

    // --- ARTWORKS CRUD -----------------------------------------------------

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
        try {
            $oldRecord = $id ? $this->artworkModel->find($id) : null;

            if ($uploadedUrl = $this->handleImageUpload($file)) {
                $data['image_path'] = $uploadedUrl;
            }

            if ($id) {
                if ($oldRecord && isset($data['image_path']) && $data['image_path'] !== $oldRecord->image_path) {
                    $this->deleteFileIfUploaded($oldRecord->image_path);
                }
                return $this->artworkModel->update($id, $data);
            }
            return $this->artworkModel->insert($data, false) !== false;
        } catch (\Exception $e) {
            log_message('error', '[BarakaAdminService] Failed to save artwork: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteArtwork(int $id): bool
    {
        $record = $this->artworkModel->find($id);
        if ($record && $this->artworkModel->delete($id)) {
            $this->deleteFileIfUploaded($record->image_path);
            return true;
        }
        return false;
    }
    
    // --- SERVICES CRUD -----------------------------------------------------

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
        try {
            $oldRecord = $id ? $this->serviceModel->find($id) : null;

            if ($uploadedUrl = $this->handleImageUpload($file)) {
                $data['icon_or_image'] = $uploadedUrl;
            }

            if ($id) {
                // If a new file was uploaded OR the user cleared the URL field
                if ($oldRecord && isset($data['icon_or_image']) && $data['icon_or_image'] !== $oldRecord->icon_or_image) {
                    $this->deleteFileIfUploaded($oldRecord->icon_or_image);
                }
                return $this->serviceModel->update($id, $data);
            }
            return $this->serviceModel->insert($data, false) !== false;
        } catch (\Exception $e) {
            log_message('error', '[BarakaAdminService] Failed to save service: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteService(int $id): bool
    {
        $record = $this->serviceModel->find($id);
        if ($record && $this->serviceModel->delete($id)) {
            $this->deleteFileIfUploaded($record->icon_or_image);
            return true;
        }
        return false;
    }

    // --- WORKSHOPS CRUD ----------------------------------------------------

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
        try {
            $oldRecord = $id ? $this->workshopModel->find($id) : null;

            if ($uploadedUrl = $this->handleImageUpload($file)) {
                $data['image_path'] = $uploadedUrl;
            }

            if ($id) {
                if ($oldRecord && isset($data['image_path']) && $data['image_path'] !== $oldRecord->image_path) {
                    $this->deleteFileIfUploaded($oldRecord->image_path);
                }
                return $this->workshopModel->update($id, $data);
            }
            return $this->workshopModel->insert($data, false) !== false;
        } catch (\Exception $e) {
            log_message('error', '[BarakaAdminService] Failed to save workshop: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteWorkshop(int $id): bool
    {
        $record = $this->workshopModel->find($id);
        if ($record && $this->workshopModel->delete($id)) {
            $this->deleteFileIfUploaded($record->image_path);
            return true;
        }
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
}

