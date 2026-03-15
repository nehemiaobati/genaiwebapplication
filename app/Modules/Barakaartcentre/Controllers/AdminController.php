<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Controllers;

use App\Controllers\BaseController;
use App\Modules\Barakaartcentre\Libraries\BarakaAdminService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class AdminController
 * Handles all admin-facing routes. Routes to this controller must be protected by an Auth Filter.
 */
class AdminController extends BaseController
{
    protected BarakaAdminService $adminService;

    public function __construct()
    {
        $this->adminService = new BarakaAdminService();
    }

    /**
     * Helper method to generate default SEO data for admin views (hidden from search).
     */
    private function getAdminSeoData(string $title): array
    {
        return [
            'pageTitle'       => "Baraka Admin | $title",
            'metaDescription' => 'Admin Dashboard',
            'canonicalUrl'    => current_url(),
            'robotsTag'       => 'noindex, nofollow',
            'metaImage'       => '',
            'admin_name'      => session()->get('baraka_admin_name'),
        ];
    }

    public function dashboard(): string
    {
        $data = $this->getAdminSeoData('Dashboard');
        $data['stats'] = $this->adminService->getDashboardStats();

        return view('App\Modules\Barakaartcentre\Views\admin\dashboard', $data);
    }

    public function services(): string
    {
        $data = $this->getAdminSeoData('Manage Services');
        $data['services'] = $this->adminService->getAllServices();

        return view('App\Modules\Barakaartcentre\Views\admin\services', $data);
    }

    public function artworks(): string
    {
        $data = $this->getAdminSeoData('Manage Artworks');
        $data['artworks'] = $this->adminService->getAllArtworks();

        return view('App\Modules\Barakaartcentre\Views\admin\artworks', $data);
    }

    public function workshops(): string
    {
        $data = $this->getAdminSeoData('Manage Workshops');
        $data['workshops'] = $this->adminService->getAllWorkshops();

        return view('App\Modules\Barakaartcentre\Views\admin\workshops', $data);
    }

    public function signups(): string
    {
        $data = $this->getAdminSeoData('View Signups');
        $data['signups'] = $this->adminService->getAllSignups();

        return view('App\Modules\Barakaartcentre\Views\admin\signups', $data);
    }

    // --- ARTWORKS ---------------------------------------------------------------- //

    public function createArtwork(): string
    {
        $data = $this->getAdminSeoData('Create Artwork');
        return view('App\Modules\Barakaartcentre\Views\admin\form_artwork', $data);
    }

    public function storeArtwork(): \CodeIgniter\HTTP\RedirectResponse
    {
        $postData = $this->request->getPost();
        // Strict typing/casting for booleans and numerics
        $postData['is_sold'] = isset($postData['is_sold']) ? 1 : 0;
        $postData['price'] = !empty($postData['price']) ? (float) $postData['price'] : null;
        $file = $this->request->getFile('image_upload');

        if ($this->adminService->saveArtwork($postData, null, $file)) {
            return redirect()->route('baraka.admin.artworks')->with('status', 'Artwork saved successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to save artwork. Please try again.');
    }

    public function editArtwork(string $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $artwork = $this->adminService->getArtworkById((int)$id);
        if (!$artwork) {
            return redirect()->route('baraka.admin.artworks')->with('error', 'Artwork not found.');
        }

        $data = $this->getAdminSeoData('Edit Artwork');
        $data['artwork'] = $artwork;
        return view('App\Modules\Barakaartcentre\Views\admin\form_artwork', $data);
    }

    public function updateArtwork(string $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $postData = $this->request->getPost();
        $postData['is_sold'] = isset($postData['is_sold']) ? 1 : 0;
        $postData['price'] = !empty($postData['price']) ? (float) $postData['price'] : null;
        $file = $this->request->getFile('image_upload');

        if ($this->adminService->saveArtwork($postData, (int)$id, $file)) {
            return redirect()->route('baraka.admin.artworks')->with('status', 'Artwork updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update artwork.');
    }

    public function deleteArtwork(string $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $artworkId = (int)$id;
        
        // Safety check: Don't delete if it has unresolved successful orders
        if ($this->adminService->hasUnresolvedOrders('artwork', $artworkId)) {
            return redirect()->back()->with('error', 'Cannot delete this artwork because it has outstanding (unresolved) successful orders. Please resolve the orders first.');
        }

        if ($this->adminService->deleteArtwork($artworkId)) {
            return redirect()->route('baraka.admin.artworks')->with('status', 'Artwork deleted.');
        }
        return redirect()->back()->with('error', 'Failed to delete artwork.');
    }

    // --- SERVICES ---------------------------------------------------------------- //

    public function createService(): string
    {
        $data = $this->getAdminSeoData('Create Service');
        return view('App\Modules\Barakaartcentre\Views\admin\form_service', $data);
    }

    public function storeService(): \CodeIgniter\HTTP\RedirectResponse
    {
        $file = $this->request->getFile('image_upload');
        if ($this->adminService->saveService($this->request->getPost(), null, $file)) {
            return redirect()->route('baraka.admin.services')->with('status', 'Service saved successfully.');
        }
        return redirect()->back()->withInput()->with('error', 'Failed to save service.');
    }

    public function editService(string $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $service = $this->adminService->getServiceById((int)$id);
        if (!$service) {
            return redirect()->route('baraka.admin.services')->with('error', 'Service not found.');
        }

        $data = $this->getAdminSeoData('Edit Service');
        $data['service'] = $service;
        return view('App\Modules\Barakaartcentre\Views\admin\form_service', $data);
    }

    public function updateService(string $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $file = $this->request->getFile('image_upload');
        if ($this->adminService->saveService($this->request->getPost(), (int)$id, $file)) {
            return redirect()->route('baraka.admin.services')->with('status', 'Service updated successfully.');
        }
        return redirect()->back()->withInput()->with('error', 'Failed to update service.');
    }

    public function deleteService(string $id): \CodeIgniter\HTTP\RedirectResponse
    {
        if ($this->adminService->deleteService((int)$id)) {
            return redirect()->route('baraka.admin.services')->with('status', 'Service deleted.');
        }
        return redirect()->back()->with('error', 'Failed to delete service.');
    }

    // --- WORKSHOPS --------------------------------------------------------------- //

    public function createWorkshop(): string
    {
        $data = $this->getAdminSeoData('Schedule Workshop');
        return view('App\Modules\Barakaartcentre\Views\admin\form_workshop', $data);
    }

    public function storeWorkshop(): \CodeIgniter\HTTP\RedirectResponse
    {
        $file = $this->request->getFile('image_upload');
        if ($this->adminService->saveWorkshop($this->request->getPost(), null, $file)) {
            return redirect()->route('baraka.admin.workshops')->with('status', 'Workshop scheduled successfully.');
        }
        return redirect()->back()->withInput()->with('error', 'Failed to schedule workshop.');
    }

    public function editWorkshop(string $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $workshop = $this->adminService->getWorkshopById((int)$id);
        if (!$workshop) {
            return redirect()->route('baraka.admin.workshops')->with('error', 'Workshop not found.');
        }

        $data = $this->getAdminSeoData('Edit Workshop');
        $data['workshop'] = $workshop;
        return view('App\Modules\Barakaartcentre\Views\admin\form_workshop', $data);
    }

    public function updateWorkshop(string $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $file = $this->request->getFile('image_upload');
        if ($this->adminService->saveWorkshop($this->request->getPost(), (int)$id, $file)) {
            return redirect()->route('baraka.admin.workshops')->with('status', 'Workshop updated successfully.');
        }
        return redirect()->back()->withInput()->with('error', 'Failed to update workshop.');
    }

    public function deleteWorkshop(string $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $workshopId = (int)$id;

        // Safety check: Don't delete if it has unresolved successful orders
        if ($this->adminService->hasUnresolvedOrders('workshop', $workshopId)) {
            return redirect()->back()->with('error', 'Cannot delete this workshop because it has outstanding (unresolved) paid attendees. Please fulfill and resolve the orders first.');
        }

        if ($this->adminService->deleteWorkshop($workshopId)) {
            return redirect()->route('baraka.admin.workshops')->with('status', 'Workshop cancelled/deleted.');
        }
        return redirect()->back()->with('error', 'Failed to delete workshop.');
    }

    // --- SIGNUPS ----------------------------------------------------------------- //

    public function deleteSignup(string $id): \CodeIgniter\HTTP\RedirectResponse
    {
        if ($this->adminService->deleteSignup((int)$id)) {
            return redirect()->route('baraka.admin.signups')->with('status', 'Signup record removed.');
        }
        return redirect()->back()->with('error', 'Failed to remove signup.');
    }

    // --- PAYMENTS & ORDERS ------------------------------------------------------- //

    public function payments(): string
    {
        $data = $this->getAdminSeoData('Payments & Orders');
        $all_orders = $this->adminService->getAllOrders();

        $data['artwork_orders']  = array_filter($all_orders, fn($o) => $o->item_type === 'artwork');
        $data['workshop_orders'] = array_filter($all_orders, fn($o) => $o->item_type === 'workshop');

        return view('App\Modules\Barakaartcentre\Views\admin\payments', $data);
    }

    public function resolveOrder(string $id): \CodeIgniter\HTTP\RedirectResponse
    {
        if ($this->adminService->resolveOrder((int)$id)) {
            return redirect()->route('baraka.admin.payments')->with('status', 'Order marked as resolved/fulfilled.');
        }
        return redirect()->back()->with('error', 'Failed to update order status.');
    }
}

