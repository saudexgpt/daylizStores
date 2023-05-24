<template>
  <div>
    <div v-if="comparedItems.length > 0">
      <h3>Compare Product List</h3>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Item</th>
            <th>Amt</th>
            <th />
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in comparedItems" :key="index">
            <td>
              <img :src="item.media[0].link" width="50"><br>
              <strong>{{ item.name }}</strong>
            </td>
            <td>
              <div>{{ '₦' + formatNumber(item.price.amount, 2) }}</div>
            </td>
            <td>
              <el-button type="danger" circle @click="removeItem(index)"><i class="el-icon-delete" /></el-button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else align="center">
      <h1>&nbsp;</h1>
      <!-- <img src="/images/empty-cart.png"> -->
      <i class="fas fa-random fa-10x" />
      <h3>Your compare items list is empty. <br>Kindly shop for items</h3>
      <button class="btn btn-primary" @click="$router.push({ path: '/product/list' }); $emit('close')">Shop Now</button>
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
    comparedItems() {
      return this.$store.getters.comparedItems;
    },
  },
  methods: {
    formatNumber,
    removeItem(index) {
      const app = this;
      const unsyc_data = app.comparedItems;
      unsyc_data.splice(index, 1);
      app.$store.dispatch('order/setComparedItems', unsyc_data);
    },
  },
};
</script>
