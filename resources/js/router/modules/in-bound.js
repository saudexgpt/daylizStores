import Layout from '@/layout';

const permissionRoutes = {
  path: '/food-menu',
  component: Layout,
  redirect: 'noredirect',
  alwaysShow: true, // will always show the root menu
  meta: {
    title: 'Manage Products',
    icon: 'el-icon-menu',
    // permissions: ['view-menu-warehouse'],
    permissions: ['create menu'],
  },
  children: [

    {
      path: 'category',
      component: () => import('@/app/stock/item-category/ItemCategory'),
      name: 'ItemCategory',
      meta: {
        title: 'Categories',
      },
    },
    {
      path: 'manage-items',
      component: () => import('@/app/stock/item/ManageItem'),
      name: 'ManageItem',
      meta: {
        title: 'Products',
      },
    },

  ],
};

export default permissionRoutes;
