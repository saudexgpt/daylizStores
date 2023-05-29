import Vue from 'vue';
import Router from 'vue-router';

Vue.use(Router);

/* Layout */
import Layout from '@/layout';
import PublicLayout from '@/layout/Public';
import adminRoutes from './modules/admin';
import errorRoutes from './modules/error';
import InBoundRoutes from './modules/in-bound';
import OrderRoutes from './modules/orders';
/**
 * Sub-menu only appear when children.length>=1
 * @see https://doc.laravue.dev/guide/essentials/router-and-nav.html
 **/

/**
* hidden: true                   if `hidden:true` will not show in the sidebar(default is false)
* alwaysShow: true               if set true, will always show the root menu, whatever its child routes length
*                                if not set alwaysShow, only more than one route under the children
*                                it will becomes nested mode, otherwise not show the root menu
* redirect: noredirect           if `redirect:noredirect` will no redirect in the breadcrumb
* name:'router-name'             the name is used by <keep-alive> (must set!!!)
* meta : {
    roles: ['admin', 'editor']   Visible for these roles only
    permissions: ['view menu zip', 'manage user'] Visible for these permissions only
    title: 'title'               the name show in sub-menu and breadcrumb (recommend set)
    icon: 'svg-name'             the icon show in the sidebar
    noCache: true                if true, the page will no be cached(default is false)
    breadcrumb: false            if false, the item will hidden in breadcrumb (default is true)
    affix: true                  if true, the tag will affix in the tags-view
  }
**/

export const constantRoutes = [{
  path: '/redirect',
  component: Layout,
  hidden: true,
  children: [{
    path: '/redirect/:path*',
    component: () =>
      import ('@/views/redirect/index'),
  }],
},
{
  path: '/login',
  component: () =>
    import ('@/app/login/index'),
  hidden: true,
},
{
  path: '/reset-password',
  component: () => import('@/app/login/ResetPassword'),
  hidden: true,
},
{
  path: '/notifications',
  component: Layout,
  hidden: true,
  meta: {
    title: 'Notifications',
    icon: 'el-icon-bell',
    // permissions: ['view audit trail'],
  },
  children: [{
    path: '',
    component: () =>
      import ('@/app/reports/Notifications'),

  }],

},
{
  path: '/auth-redirect',
  component: () =>
    import ('@/app/login/AuthRedirect'),
  hidden: true,
},
{
  path: '/404',
  redirect: { name: 'Page404' },
  component: () =>
    import ('@/views/error-page/404'),
  hidden: true,
},
{
  path: '/401',
  component: () =>
    import ('@/views/error-page/401'),
  hidden: true,
},
{
  path: '/dashboard',
  component: Layout,
  redirect: 'dashboard/index',
  children: [{
    path: 'index',
    component: () =>
      import ('@/app/dashboard/index'),
    name: 'Dashboard',
    meta: { title: 'dashboard', icon: 'el-icon-s-home', noCache: false },
  },

  ],
},
{
  path: '',
  component: PublicLayout,
  redirect: 'home',
  hidden: true,
  children: [{
    path: 'home',
    component: () =>
      import ('@/pages/index'),
    name: 'Home',
  },

  ],
},
{
  path: '/product',
  component: PublicLayout,
  redirect: 'product/list',
  hidden: true,
  children: [{
    path: 'list',
    component: () =>
      import ('@/pages/ProductList'),
    name: 'Menu',
  },
  {
    path: 'check-out',
    component: () =>
      import ('@/pages/CheckOut'),
    name: 'CheckOut',
  },
  {
    path: 'details/:slug',
    component: () => import('@/pages/ItemDetails'),
    name: 'ProductDetails',
    meta: { title: 'Product Details' },
  },
  {
    path: 'search/:slug',
    component: () => import('@/pages/ProductSearch'),
    name: 'ProductSearch',
    meta: { title: 'Product Search' },
  },
  {
    path: 'category/:categoryId',
    component: () => import('@/pages/ProductList'),
    name: 'CategorizedItems',
  },

  ],
},
{
  path: '/track',
  component: PublicLayout,
  redirect: 'track/order',
  hidden: true,
  children: [{
    path: 'order',
    component: () =>
      import ('@/pages/TrackOrder'),
    name: 'TrackOrder',
  },

  ],
},
{
  path: '/about',
  component: PublicLayout,
  redirect: 'about/',
  hidden: true,
  children: [{
    path: '/',
    component: () =>
      import ('@/pages/About'),
    name: 'AboutUs',
  },

  ],
},
{
  path: '/profile',
  component: Layout,
  redirect: '/profile/edit',
  hidden: true,
  children: [{
    path: 'edit',
    component: () =>
      import ('@/app/users/SelfProfile'),
    name: 'SelfProfile',
    meta: { title: 'userProfile', icon: 'user', noCache: true },
  }],
},
{
  path: '/my-account',
  component: PublicLayout,
  redirect: '/my-account/edit',
  hidden: true,
  children: [{
    path: 'edit',
    component: () =>
      import ('@/app/users/SelfProfile'),
    name: 'MyAccount',
    meta: { title: 'userProfile', icon: 'user', noCache: true },
  }],
},
{
  path: '/default-password',
  component: Layout,
  redirect: '/default-password/change',
  hidden: true,
  children: [{
    path: 'change',
    // redirect: 'dashboard',
    component: () =>
      import ('@/app/users/ChangeDefaultPassword'),
    hidden: true,
  }],
},
];

export const asyncRoutes = [
  InBoundRoutes,
  OrderRoutes,
  adminRoutes,
  errorRoutes,
  { path: '*', redirect: '/404', hidden: true },
];

const createRouter = () => new Router({
  mode: 'history', // require service support
  scrollBehavior: () => ({ y: 0 }),
  base: process.env.MIX_LARAVUE_PATH,
  routes: constantRoutes,
});

const router = createRouter();

// Detail see: https://github.com/vuejs/vue-router/issues/1234#issuecomment-357941465
export function resetRouter() {
  const newRouter = createRouter();
  router.matcher = newRouter.matcher; // reset router
}

export default router;

