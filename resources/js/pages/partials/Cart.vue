<template>
  <div>
    <div v-if="cart.length > 0">
      <h3>My Cart Content</h3>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Item</th>
            <th>QTY/Amt</th>
            <!-- <th>Extra</th> -->
            <th />
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in cart" :key="index">
            <td>
              <img :src="item.media[0].link" width="100"><br>
              <strong>{{ item.name }}</strong>
            </td>
            <td>
              <el-input-number v-model="item.quantity" controls-position="right" :min="1" size="small" @input="calculateTotal()" /><br>

              <div :id="`rate_${item.id}`">{{ '₦' + formatNumber(item.price.amount, 2) }}</div>
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
    setOtherItemDescription(itemStocks) {
      const colors = [];
      const sizes = [];
      itemStocks.forEach(stock => {
        if (stock.color !== null) {
          colors.push(stock.color);
        }
        if (stock.size !== null) {
          sizes.push(stock.size);
        }
      });
      return [colors, sizes];
    },
    calculateTotal(){
      const app = this;
      let amount = 0;
      app.cart.forEach(item => {
        const quantity = item.quantity;
        const standardAmount = item.price.amount;
        item.rate = standardAmount;
        const rate_change = document.getElementById(`rate_${item.id}`);
        if (rate_change) {
          rate_change.innerHTML = '₦' + app.formatNumber(standardAmount, 2);
        }
        // document.getElementById(`rate_${item.id}`).innerHTML = '₦' + app.formatNumber(standardAmount, 2);
        let price = parseInt(quantity * standardAmount);
        if (item.discounts.length > 0) {
          item.discounts.forEach(discount => {
            const moq = discount.minimum_order_quantity;
            const discountedAmount = discount.amount;
            if (quantity >= moq) {
              price = parseInt(quantity * discountedAmount);
              if (rate_change) {
                rate_change.innerHTML = '₦' + app.formatNumber(discountedAmount, 2);
              }
              // document.getElementById(`rate_${item.id}`).innerHTML = '₦' + app.formatNumber(discountedAmount, 2);
              item.rate = discountedAmount;
            }
          });
        }
        amount += price;
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
