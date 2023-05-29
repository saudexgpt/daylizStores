<template>
  <div>
    <div v-if="cart.length > 0">
      <h3>My Cart Content</h3>
      <table class="table">
        <!-- <thead>
          <tr>
            <th>Item</th>
            <th>QTY/Amt</th>
            <th />
          </tr>
        </thead> -->
        <tbody>
          <tr v-for="(item, index) in cart" :key="index">
            <td>
              <img :src="item.media[0].link" width="100">
            </td>
            <td>
              <strong>{{ item.name }}</strong>
              <p><label>{{ item.quantity }} pieces</label></p>
              <span v-if="item.standardAmount !== item.rate">
                <span><s>{{ '₦' + formatNumber(item.standardAmount, 2) }}</s></span>
                <span><label style="color: brown">{{ '₦' + formatNumber(item.rate, 2) }}</label></span>
              </span>
              <span v-else><label style="color: brown">{{ '₦' + formatNumber(item.rate, 2) }}</label></span>
            </td>
            <td>
              <el-button type="danger" circle @click="removeItem(index)"><i class="el-icon-delete" /></el-button>
            </td>
          </tr>
          <tr>
            <td colspan="4" align="right"><h3>Total: <strong>{{ '₦' + formatNumber(form.amount, 2) }}</strong></h3></td>
          </tr>
          <tr>
            <td colspan="4" align="right">
              <button class="btn btn-primary" @click="shop();">Continue Shopping</button>
              <button class="btn btn-success" @click="checkOut()">
                Check Out
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else align="center">
      <h1>&nbsp;</h1>
      <img src="/images/empty-cart.png">
      <h3>Your cart is empty. <br>Kindly shop for items</h3>
      <button class="btn btn-primary" @click="shop()">Shop Now</button>
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
    cart() {
      return this.$store.getters.cart;
    },
  },
  created() {
    this.calculateTotal();
  },
  methods: {
    formatNumber,
    // calculateTotalStockBal(item) {
    //   let total_stock_balance = 0;
    //   item.item_stocks.forEach(stock => {
    //     total_stock_balance += parseInt(stock.total_stock_balance - stock.sold);
    //   });
    //   return total_stock_balance;
    // },
    calculateTotal(){
      const app = this;
      let amount = 0;
      app.cart.forEach(item => {
        const subTotal = item.subTotal;
        amount += subTotal;
      });
      app.form.amount = amount;
    },
    removeItem(index) {
      const app = this;
      const unsyc_data = app.cart;
      unsyc_data.splice(index, 1);
      app.calculateTotal();
      app.$store.dispatch('order/setCartItems', unsyc_data);
      const form = app.form;
      form.cart_items = unsyc_data;
      app.$store.dispatch('order/setPendingOrder', form);
    },
    shop() {
      const app = this;
      app.$emit('close');
      if (app.$route.name !== 'Menu') {
        app.$router.push({ path: '/product/list' });
      }
    },
    checkOut() {
      const app = this;
      app.calculateTotal();
      const form = app.form;
      form.cart_items = app.cart;
      app.$store.dispatch('order/setCartItems', app.cart);
      app.$store.dispatch('order/setPendingOrder', form);
      app.$emit('close');
      if (app.$route.name !== 'CheckOut') {
        app.$router.push({ path: '/product/check-out' });
      }
    },
  },
};
</script>
