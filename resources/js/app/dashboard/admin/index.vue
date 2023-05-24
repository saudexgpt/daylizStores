<template>
  <div class="dashboard-editor-container">
    <panel-group v-if="data_summary" :data-summary="data_summary" />

    <order />
  </div>
</template>

<script>
import PanelGroup from './components/PanelGroup';
import Order from '@/app/order';
import Resource from '@/api/resource';
const adminDashboard = new Resource('dashboard/admin');

export default {
  name: 'AdminDashboard',
  components: {
    PanelGroup,
    Order,
  },
  data() {
    return {
      data_summary: '',
      warehouses: [],
    };
  },
  mounted() {
    this.fetchDashboardDetails();
  },
  methods: {
    fetchDashboardDetails() {
      const app = this;
      adminDashboard.list()
        .then(response => {
          app.data_summary = response.data_summary;
          app.warehouses = response.warehouses;
        });
    },
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
