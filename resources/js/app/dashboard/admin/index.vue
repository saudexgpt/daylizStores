<template>
  <div class="dashboard-editor-container">
    <panel-group v-if="data_summary" :data-summary="data_summary" />

    <order />
    <!-- <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Product</th>
          <th>Stock Balance</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(stock, index) in running_out_of_stock_products" :key="index">
          <td>{{ stock.item.name }}</td>
          <td>{{ stock.total_balance }}</td>
        </tr>
      </tbody>
    </table> -->
  </div>
</template>

<script>
import PanelGroup from './components/PanelGroup';
import Order from '@/app/order';
import Resource from '@/api/resource';

export default {
  name: 'AdminDashboard',
  components: {
    PanelGroup,
    Order,
  },
  data() {
    return {
      data_summary: '',
      running_out_of_stock_products: [],
    };
  },
  mounted() {
    this.fetchDashboardDetails();
    // this.runningOutOfStockProducts();
  },
  methods: {
    fetchDashboardDetails() {
      const app = this;
      const adminDashboard = new Resource('dashboard/admin');
      adminDashboard.list()
        .then(response => {
          app.data_summary = response.data_summary;
        });
    },
    // runningOutOfStockProducts() {
    //   const app = this;

    //   const outOfStochProductsResource = new Resource('dashboard/admin/running-out-of-stock-products');
    //   outOfStochProductsResource.list()
    //     .then(response => {
    //       app.running_out_of_stock_products = response.running_out_of_stock_products;
    //     });
    // },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.dashboard-editor-container {
  padding: 10px;
  background-color: rgb(240, 242, 245);
  .chart-wrapper {
    background: #fff;
    padding: 16px 16px 0;
    margin-bottom: 10px;
  }
}
</style>
