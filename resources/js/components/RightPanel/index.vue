<template>
  <div ref="rightPanel" :class="{show:show}" class="rightPanel-container">
    <div class="rightPanel-background" />
    <div class="rightPanel">
      <div class="handle-button" :style="{'top':buttonTop+'px','background-color':theme}" @click="show=true; view = 'cart'">
        <el-tooltip class="item" effect="dark" content="Cart" placement="left-start">
          <el-badge :value="cart.length" type="success">
            <i v-if="cart.length > 0" class="el-icon-shopping-cart-full fa-2x" />
            <i v-else class="el-icon-shopping-cart-2" />
          </el-badge>
        </el-tooltip>
      </div>
      <div class="handle-button" :style="{'top':300+'px','background-color':secondaryTheme}" @click="show=true; view = 'wish_list'">
        <el-tooltip class="item" effect="dark" content="Wish List" placement="left-start">
          <el-badge :value="wishList.length" type="success">
            <i class="fas fa-heart fa-2x text-white" />
          </el-badge>
        </el-tooltip>
      </div>
      <!-- <div class="handle-button" :style="{'top':350+'px','background-color':theme}" @click="show=true; view = 'compare'">
        <el-tooltip class="item" effect="dark" content="Compare" placement="left-start">
          <el-badge :value="comparedItems.length" type="success">
            <i class="fas fa-random fa-2x text-white" />
          </el-badge>
        </el-tooltip>
      </div> -->
      <div class="rightPanel-items padded" style="height: 550px; overflow: auto;">
        <div class="pull-right" @click="togglePanel()">
          <i class="el-icon-close fa-2x" />
        </div>
        <!-- <slot /> -->
        <cart v-if="view === 'cart'" @close="togglePanel" />
        <compare v-if="view === 'compare'" @close="togglePanel" />
        <wish-list v-if="view === 'wish_list'" @close="togglePanel" />
      </div>
    </div>
  </div>
</template>

<script>
import Cart from '@/pages/partials/Cart';
import Compare from '@/pages/partials/Compare';
import WishList from '@/pages/partials/WishList';
import { formatNumber } from '@/utils/index';
export default {
  name: 'RightPanel',
  components: {
    Cart, Compare, WishList,
  },
  props: {
    clickNotClose: {
      default: false,
      type: Boolean,
    },
    buttonTop: {
      default: 250,
      type: Number,
    },
  },
  data() {
    return {
      show: false,
      view: '',
      form: {
        cart_items: [],
        amount: 0,
      },
    };
  },
  computed: {
    theme() {
      return this.$store.state.settings.theme;
    },
    secondaryTheme() {
      return this.$store.state.settings.secondaryTheme;
    },
    cart() {
      return this.$store.getters.cart;
    },
    wishList() {
      return this.$store.getters.wishList;
    },
    comparedItems() {
      return this.$store.getters.comparedItems;
    },
  },
  // watch: {
  //   show(value) {
  //     if (value && !this.clickNotClose) {
  //       this.addEventClick();
  //     }
  //     if (value) {
  //       addClass(document.body, 'showRightPanel');
  //     } else {
  //       removeClass(document.body, 'showRightPanel');
  //     }
  //   },
  // },
  mounted() {
    this.show = this.showPanel;
    this.insertToBody();
  },
  beforeDestroy() {
    const elx = this.$refs.rightPanel;
    elx.remove();
  },
  methods: {
    formatNumber,
    addEventClick() {
      window.addEventListener('click', this.closeSidebar);
    },
    closeSidebar(evt) {
      const parent = evt.target.closest('.rightPanel');
      if (!parent) {
        this.show = false;
        window.removeEventListener('click', this.closeSidebar);
      }
    },
    insertToBody() {
      const elx = this.$refs.rightPanel;
      const body = document.querySelector('body');
      body.insertBefore(elx, body.firstChild);
    },
    togglePanel() {
      const app = this;
      app.show = !app.show;
      app.view = '';
    },
  },
};
</script>

<style>
.showRightPanel {
  overflow: hidden;
  position: relative;
  width: calc(100% - 15px);
}
</style>

<style lang="scss" scoped>
.rightPanel-background {
  opacity: 0;
  transition: opacity .3s cubic-bezier(.7, .3, .1, 1);
  background: rgba(0, 0, 0, .2);
  width: 0;
  height: 0;
  top: 0;
  left: 0;
  position: fixed;
  z-index: -1;
}

.rightPanel {
  background: #fff;
  z-index: 3000;
  position: fixed;
  height: 100vh;
  width: 100%;
  max-width: 460px;
  top: 0px;
  left: 0px;
  box-shadow: 0px 0px 15px 0px rgba(0, 0, 0, .05);
  transition: all .25s cubic-bezier(.7, .3, .1, 1);
  transform: translate(100%);
  z-index: 100;
  left: auto;
  right: 0px;
}

.show {
  transition: all .3s cubic-bezier(.7, .3, .1, 1);

  .rightPanel-background {
    z-index: 50;
    opacity: 1;
    width: 100%;
    height: 100%;
  }

  .rightPanel {
    transform: translate(0);
  }
}

.handle-button {
  position: absolute;
  left: -48px;
  border-radius: 6px 0 0 6px !important;
  width: 48px;
  height: 48px;
  pointer-events: auto;
  z-index: 0;
  cursor: pointer;
  pointer-events: auto;
  font-size: 24px;
  text-align: center;
  color: #fff;
  line-height: 48px;

  i {
    font-size: 24px;
    line-height: 48px;
  }
}
</style>
