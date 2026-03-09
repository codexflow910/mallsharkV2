<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'Vendor Management' => [
                'approve-vendor','reject-vendor','view-vendors','view-tiktok-videos', 'contact-seller', 'enable-vendor',
                'access-store', 'activate-vendor', 'disable-vendor', 'deactivate-vendor', 'enable-ai',
            ],
            'User Management' => [
                'view-users','create-user','edit-user','delete-user',
            ],
            'Product Management' => [
                'approve-product','reject-product','view-products','edit-product','delete-product',
                'view-feature-products', 'manage-reviews', 'product-link-videos', 'edit-mallshark-selected',
                'manage-distributors'
            ],
            'Finance' => [
                'view-payouts','approve-payouts','view-commissions','view-invoices','view-sales-reports',
            ],
            'Content Management' => [
                'manage-categories','manage-banners','manage-cms-pages',
                'approve-product-content','manage-discounts','manage-coupons',
                'manage-youtube-videos', 'manage-seller-packages', 'manage-faqs'
            ],
            'Support' => [
                'manage-tickets','manage-disputes','view-orders','process-refunds','process-returns',
            ],
            'System' => [
                'manage-settings','manage-roles', 'reset-ai-token', 'manage-permissions'
            ],
            'Store Management' => [
                'manage-products-store', 'manage-orders-store', 'manage-discounts-store',
            ],
        ];

        // Create all permissions
        foreach ($permissions as $group => $perms) {
            foreach ($perms as $perm) {
                Permission::firstOrCreate([
                    'name' => $perm,
                    'display_name' => ucwords(str_replace('-', ' ', $perm)),
                    'group_name' => $group,
                ]);
            }
        }

        // Create roles
        $superAdmin     = Role::firstOrCreate(['role_name' => 'Super Admin']);
        $admin          = Role::firstOrCreate(['role_name' => 'Admin']);
        $financeManager = Role::firstOrCreate(['role_name' => 'Finance Manager']);
        $contentManager = Role::firstOrCreate(['role_name' => 'Content Manager']);
        $supportManager = Role::firstOrCreate(['role_name' => 'Support Manager']);

        // Assign permissions by syncing their IDs
        $superAdmin->permissions()->sync(Permission::pluck('permission_id'));

        $admin->permissions()->sync(
            Permission::whereIn('name', [
                'approve-vendor','reject-vendor','view-vendors','view-tiktok-videos',
                'view-users','create-user','edit-user','delete-user', 'edit-mallshark-selected',
                'approve-product','reject-product','view-products','edit-product','delete-product',
                'manage-products-store', 'manage-orders-store', 'manage-discounts-store',
            ])->pluck('permission_id') 
        );

        $financeManager->permissions()->sync(
            Permission::whereIn('name', [
                'view-payouts','approve-payouts','view-commissions','view-invoices','view-sales-reports',
            ])->pluck('permission_id')
        );

        $contentManager->permissions()->sync(
            Permission::whereIn('name', [
                'manage-categories','manage-banners','manage-cms-pages',
                'approve-product-content','manage-discounts','manage-coupons',
            ])->pluck('permission_id')
        );

        $supportManager->permissions()->sync(
            Permission::whereIn('name', [
                'manage-tickets','manage-disputes','view-orders','process-refunds','process-returns',
            ])->pluck('permission_id')
        );
    }
}
