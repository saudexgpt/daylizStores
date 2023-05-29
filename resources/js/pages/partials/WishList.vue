<template>
  <div>
    <div v-if="wishList.length > 0">
      <h3>My Wish List</h3>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Item</th>
            <th>Amt</th>
            <th />
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in wishList" :key="index">
            <td>
              <img :src="item.media[0].link" width="100"><br>
              <strong>{{ item.name }}</strong>
            </td>
            <td>
              <div>{{ '₦' + formatNumber(item.price.amount, 2) }}</div>
            </td>
            <td>
              <!-- <el-tooltip effect="dark" content="Add to cart" placement="bottom-start">
                <el-button :disabled="item.item_stocks.length < 1" circle type="primary" @click="addItemToCart(item, index)"><i class="el-icon-shopping-cart-2" /> </el-button>
              </el-tooltip> -->
              <el-tooltip effect="dark" content="View Details" placement="top-start">
                <el-button circle type="primary" @click="viewItemDetails()"><i class="el-icon-view" /> </el-button>
              </el-tooltip>
              <el-tooltip effect="dark" content="Remove from wishlist" placement="bottom-start">
                <el-button type="danger" circle @click="removeItem(index)"><i class="el-icon-delete" /></el-button>
              </el-tooltip>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else align="center">
      <h1>&nbsp;</h1>
      <!-- <img src="/images/empty-cart.png"> -->
      <i class="fas fa-heart fa-10x" />
      <h3>Your wish list is empty. <br>Kindly shop for items</h3>
      <button class="btn btn-primary" @click="shop();">Shop Now</button>
    </div>
  </div>
</template>
<script>
import { formatNumber } from '@/utils/index';
// import { addClass, removeClass } from '@/utils';

export default {
  name: 'RightPanel',
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
      form: {
        cart_items: [],
        amount: 0,
      },
    };
  },
  computed: {
    wishList() {
      return this.$store.getters.wishList;
    },
  },
  methods: {
    formatNumber,
    addItemToCart(item, index) {
      item.quantity = 1;
      this.$store.dispatch('order/addItemToCart', item);
      this.removeItem(index);
      this.$notify({
        title: `${item.name} is added to cart`,
      });
    },
    shop() {
      const app = this;
      if (app.$route.name !== 'Menu') {
        app.$router.push({ path: '/product/list' });
      }
      app.$emit('close');
    },
    removeItem(index) {
      const app = this;
      const unsyc_data = app.wishList;
      unsyc_data.splice(index, 1);
      app.$store.dispatch('order/setWishlist', unsyc_data);
    },
    viewItemDetails(slug) {
      const app = this;
      if (app.$route.name !== 'ProductDetails') {
        app.$router.push({ name: 'ProductDetails', params: { slug }});
      }
      app.$emit('close');
    },
  },
};
</script>
