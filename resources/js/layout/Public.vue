<template>
  <div>
    <div class="header-most-top">
      <p class="header-link">
        <a @click="load('/home')">Home</a>&nbsp;<span style="color: #909090">|</span>
        <a @click="load('/about')">About Us</a>&nbsp;<span style="color: #909090">|</span>
        <a @click="load('/product/list')">Shop</a>
        <!-- <a @click="load('/track/order')">Track Order</a> -->
      </p>
      <p class="header-slug text-center">Your Happiness, Our Priority</p>
    </div>
    <div class="mobile-view-fixed-tab">
      <el-row>
        <el-col :xs="8">
          <div align="center">
            <a @click="load('/product/list')"> <i class="el-icon-sell fa-2x" /><br>Shop</a>
          </div>
        </el-col>
        <el-col :xs="8">
          <div align="center">
            <a @click="load('/track/order')"> <i class="el-icon-guide fa-2x" /><br>Track Order</a>
          </div>
        </el-col>
        <el-col :xs="8">
          <div align="center">
            <el-dropdown size="medium" trigger="click">
              <span class="el-dropdown-link" style="cursor: pointer">
                <i class="el-icon-more fa-2x" /><br>More
              </span>
              <el-dropdown-menu slot="dropdown" style="width: 200px !important; padding: 10px !important">
                <el-dropdown-item><a @click="load('/home')">Home</a></el-dropdown-item>
                <el-dropdown-item><a @click="load('/about')">About Us</a></el-dropdown-item>
                <el-dropdown-item><a @click="load('/my-account/edit')">My Profile</a></el-dropdown-item>
                <el-dropdown-item v-if="userData.id === null" divided><a class="btn btn-success" @click="load('/login')">SIGN IN</a></el-dropdown-item>
                <el-dropdown-item v-if="userData.id !== null" divided><a class="btn btn-danger" @click="logout">LOGOUT</a></el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
          </div>
        </el-col>
      </el-row>
    </div>
    <nav id="navbarHome" class="header-bg">

      <ul class="menu">
        <li class="toggle" @click="openNav()"><i class="el-icon-s-fold" /></li>
        <li class="logo">
          <a href="/home">
            <img :src="img" alt="Company Logo" class="logo-responsive">
          </a>
        </li>
        <li class="search-nav">
          <div>
            <search-box />
          </div>
        </li>
        <!-- <li class="track-order"><a @click="load('/track/order')">Track Order</a></li> -->
        <li class="track-order">
          <el-dropdown size="medium" trigger="click">
            <span class="el-dropdown-link text-white" style="cursor: pointer">
              <label>
                <i class="el-icon-user" /> {{ (userData.id !== null) ? userData.name : 'Account' }} <i class="el-icon-arrow-down el-icon--right" />
              </label>
            </span>
            <el-dropdown-menu slot="dropdown" style="width: 200px !important; padding: 10px !important">
              <el-dropdown-item><a @click="load('/track/order')">Track Order</a></el-dropdown-item>
              <el-dropdown-item><a @click="load('/my-account/edit')">My Profile</a></el-dropdown-item>
              <el-dropdown-item v-if="userData.id === null" divided><a class="btn btn-success" @click="load('/login')">SIGN IN</a></el-dropdown-item>
              <el-dropdown-item v-if="userData.id !== null" divided><a class="btn btn-danger" @click="logout">LOGOUT</a></el-dropdown-item>
            </el-dropdown-menu>
          </el-dropdown>
        </li>
        <!-- <li class="nav-item"><a @click="loadAndToggle('/home')">Home</a></li>
        <li class="nav-item"><a @click="loadAndToggle('/about')">About</a></li>
        <li class="nav-item"><a @click="loadAndToggle('/menu/list')">Shop</a></li> -->
        <!-- <li class="item button"><router-link to="/menu/list">Order Now</router-link></li> -->
        <!-- <li class="item button secondary"><a href="#">Sign Up</a></li> -->
        <!-- <li class="cart" style="cursor: pointer">
          <div @click="view = 'wish_list'; showCartContent = true">
            <el-tooltip class="item" effect="dark" content="Wishlist" placement="bottom-start">
              <el-badge :value="wishList.length" type="success">
                <i class="fas fa-heart fa-2x text-white" />
              </el-badge>
            </el-tooltip>
          </div>
        </li>
        <li class="cart" style="cursor: pointer">
          <div @click="view = 'compare'; showCartContent = true">
            <el-tooltip class="item" effect="dark" content="Compare" placement="bottom-start">
              <el-badge :value="comparedItems.length" type="success">
                <i class="fas fa-random fa-2x text-white" />
              </el-badge>
            </el-tooltip>
          </div>
        </li> -->
      </ul>
    </nav>
    <div class="app-container content">
      <div id="mySidenav" class="sidenav">
        <a href="javascript:void(0)" class="closebtn" @click="closeNav()">&times;</a>
        <div v-for="(category, index) in categories" :key="index">
          <a @click=" closeNav(); loadNamedUrl('CategorizedItems', { categoryId: category.id });">{{ category.name }}</a>
        </div>
      </div>
      <el-row :gutter="10">
        <el-col :xs="24" :sm="8" :md="5" :lg="5">
          <div class="hide-mobile">
            <left-menu />
          </div>
        </el-col>
        <el-col :xs="24" :sm="16" :md="19" :lg="19">
          <div class="search-body" style="margin-bottom: 10px;">
            <search-box />
          </div>
          <app-main />
        </el-col>
      </el-row>
      <right-panel :view="view" :show-panel="showCartContent" />

      <!-- <el-dialog title="Sign In" :visible.sync="showLoginForm">
        <customer-login />
      </el-dialog> -->
    </div>
    <div class="footerr header-bg text-light">
      <div class="footer">
        <el-row :gutter="20">
          <el-col
            :md="6"
            :sm="12"
            :xs="24"
          >
            <!-- <div class="hide-mobile">
              <img :src="img" alt="Company Logo" width="100">
            </div> -->
            <div v-if="params">
              <h3 class="section-title-footer ff-secondary text-start text-white fw-normal mb-4">Account Details</h3>
              <span v-html="params.account_details" />
              <div>
                <strong>Note: </strong>Any goods left unpicked is at owner's risk<br>
                NO REFUNDS after payment, NO EXCHANGE after pickup
              </div>
            </div>
          </el-col>
          <el-col
            :md="6"
            :sm="12"
            :xs="24"
          >
            <h3 class="section-title-footer ff-secondary text-start text-white fw-normal mb-4">Quick Links</h3>
            <div class="pt-2">
              <p><a class="btn btn-link" @click="load('/home')">Home</a></p>
              <p><a class="btn btn-link" @click="load('/product/list')">Shop</a></p>
              <p><a class="btn btn-link" @click="load('/about')">About Us</a></p>
              <p><a class="btn btn-link" @click="load('/track/order')">Track Order</a></p>
              <p><a class="btn btn-link" @click="load('/login')">Login</a></p>
            </div>
          </el-col>
          <el-col
            :md="6"
            :sm="12"
            :xs="24"
          >
            <h3 class="section-title-footer ff-secondary text-center text-white fw-normal mb-4">Contact</h3>
            <p class="mb-2"><i class="fa fa-map-marker-alt me-3" />
              23, Golden Bimot Plaza, Opposite Musec Filling Station,<br>
              Ashipa Road, Amule bus stop,<br>
              Ayobo-Ipaja,<br>
              Lagos, Nigeria
            </p>
            <p class="mb-2"><i class="fa fa-phone-alt me-3" /> +234 814 687 5777 | +234 913 687 7689</p>
            <p class="mb-2"><i class="fa fa-envelope me-3" /> info@daylizstores.com</p>
            <p class="mb-2"><i class="fab fa-instagram me-3" /> @dayliz_stores</p>
            <p class="mb-2"><a
              href="https://web.facebook.com/daylizcollections"
              target="_blank"
            ><i class="fab fa-facebook me-3" /> Dayliz Collections</a></p>
            <p class="mb-2"><a
              href="https://web.whatsapp.com/send?phone=+2348146875777"
              target="_blank"
            ><i class="fab fa-whatsapp me-3" /> Message us via WhatsApp</a></p>
          </el-col>
          <el-col
            :md="6"
            :sm="12"
            :xs="24"
          >
            <h3 class="section-title-footer ff-secondary text-start text-white fw-normal mb-4">Opening</h3>
            <h5 class="text-light fw-normal">Mondays - Fridays</h5>
            <p>10:00AM - 5:00PM</p><br>
            <!-- <h5 class="text-light fw-normal">Saturdays</h5>
            <p>12:00noon - 5:00PM</p> -->
          </el-col>
        </el-row>
      </div>
      <div class="bg-footer">
        <div class="copyright">
          <div class="row">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
              &copy; <a class="border-bottom" href="#">Dayliz Stores</a> All Right Reserved.
            </div>
            <div class="col-md-6 text-center text-md-end">
              Powered By <a class="border-bottom" href="https://3coretechnology.com" target="_blank">3Core Technology Limited</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script>
// import IdleModal from './IdleModal';
import { AppMain } from './components';
import RightPanel from '@/components/RightPanel';
// import { Settings } from './components';
import LeftMenu from '@/pages/partials/LeftMenu.vue';
// import Resource from '@/api/resource';
import SearchBox from '@/pages/partials/SearchBox';
export default {
  name: 'Layout',
  components: {
    LeftMenu,
    SearchBox,
    // IdleModal,
    AppMain,
    // Navbar,
    RightPanel,
    // Settings,
    // Sidebar,
    // TagsView,
  },
  data() {
    return {
      img: '/images/logo.png',
      showCartContent: false,
      form: {
        cart_items: [],
        amount: 0,
      },
      selectedCategory: '',
      searchString: '',
      view: 'cart',
    };
  },
  computed: {
    cart() {
      return this.$store.getters.cart;
    },
    wishList() {
      return this.$store.getters.wishList;
    },
    comparedItems() {
      return this.$store.getters.comparedItems;
    },
    categories() {
      return this.$store.getters.categories;
    },
    userData() {
      return this.$store.getters.userData;
    },
    params() {
      return this.$store.getters.params;
    },
  },
  mounted() {
    var navbar = document.getElementById('navbarHome');

    // Get the offset position of the navbar
    var sticky = navbar.offsetTop;

    // Add the sticky class to the navbar when you reach its scroll position. Remove "sticky" when you leave the scroll position
    function myFunction() {
      if (window.pageYOffset >= sticky) {
        navbar.classList.add('sticky');
      } else {
        navbar.classList.remove('sticky');
      }
    }

    // When the user scrolls the page, execute myFunction
    window.onscroll = function() {
      myFunction();
    };
  },
  methods: {
    openNav() {
      document.getElementById('mySidenav').style.width = '250px';
    },

    closeNav() {
      document.getElementById('mySidenav').style.width = '0';
    },
    async logout() {
      await this.$store.dispatch('user/logout');
      this.$notify({
        title: `You have successfully logged out`,
      });
      window.location = '/home';
    },
    load(url) {
      this.$router.push({ path: url });
    },
    loadNamedUrl(name, param) {
      this.$router.push({ name: name, params: param });
    },
  },
};
</script>

<style lang="scss" scoped>
  @import "~@/styles/mixin.scss";
  @import "~@/styles/variables.scss";
  @import "~@/styles/public/styles.scss";
  .app-wrapper {
    @include clearfix;
    position: relative;
    height: 100%;
    width: 100%;

    &.mobile.openSidebar {
      position: fixed;
      top: 0;
    }
  }
  .header-bg {
    background-image: url('../assets/bg/header.jpg');
  }
  // #navbar {
  //   margin-bottom: 1.5rem;
  // }
  .sticky {
    position: fixed;
    top: 0;
    z-index: 9;
    width: 100%;
    transition: width 0.28s;
  }
  .sticky + .content {
    padding-top: 60px;
  }
  // .content {
  //   padding-left: 1.5rem;
  //   padding-right: 1.5rem;
  // }
  .header-most-top p {
    text-align: right;
    color: #fff;
    font-weight: 600;
    font-family: 'Pacifico', cursive;
    padding: 8px;
    letter-spacing: 2px;
    background: #be1712;
  }
  .footer {
    padding: 40px;
  }
  .bg-footer {
    background: #be1712;
    height: 60px;
    padding: 15px;
  }
  .btn.btn-social {
    margin-right: 5px;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--light);
    border: 1px solid #FFFFFF;
    border-radius: 35px;
    transition: .3s;
  }
  .btn.btn-link {
    display: block;
    margin-bottom: 5px;
    padding: 0;
    text-align: left;
    color: #FFFFFF;
    font-size: 15px;
    font-weight: normal;
    text-transform: capitalize;
    transition: .3s;
  }
  .el-select .el-input {
    width: 110px;
  }
  .input-with-select .el-input-group__prepend {
    background-color: #fff;
  }
</style>
<style>
.sidenav {
  height: 100%;
  width: 0;
  position: fixed;
  z-index: 10;
  top: 0;
  left: 0;
  background-color: #ffffff;
  overflow-x: hidden;
  transition: 0.5s;
  padding-top: 60px;
}

.sidenav a {
  padding: 8px 8px 8px 32px;
  text-decoration: none;
  font-size: 25px;
  color: #000000;
  display: block;
  transition: 0.3s;
}

.sidenav a:hover {
  color: #666666;
}

.sidenav .closebtn {
  position: absolute;
  top: 0;
  right: 25px;
  font-size: 36px;
  margin-left: 50px;
}

@media screen and (max-height: 450px) {
  .sidenav {padding-top: 15px;}
  .sidenav a {font-size: 18px;}
}
</style>
