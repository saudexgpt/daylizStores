<template>
  <div class="app-container">
    <!-- <item-details v-if="page.option== 'view_details'" :item-in-stock="order" :page="page" /> -->
    <!-- <add-new v-if="page.option== 'add_new'" :orders="orders" :params="params" :page="page" />
    <edit-item v-if="page.option=='edit_item'" :orders="orders" :order="order" :params="params" :page="page" @update="onEditUpdate" /> -->
    <div v-if="page.option==='list'" class="box">
      <div class="box-header">
        <h4 class="box-title">List of Orders {{ in_location }}</h4>

        <span class="pull-right">
          <el-select v-model="form.status" placeholder="Select Status" class="span" @input="getOrders">
            <el-option v-for="(status, index) in params.order_statuses" :key="index" :value="status" :label="status" />

          </el-select>
        </span>

      </div>
      <div class="box-body">
        <aside>
          <el-row>
            <el-col :xs="24" :sm="24" :md="24">
              <el-input v-model="search_query" placeholder="Search by Order No. or Customer name or Customer Phone" class="no-border">
                <el-button slot="append" :disabled="search_query === ''" type="primary" icon="el-icon-search" @click="searchOrderNumber" />
              </el-input>
            </el-col>
          </el-row>
        </aside>
        <v-client-table v-model="orders" v-loading="load" :columns="columns" :options="options">
          <div slot="customer.name" slot-scope="props">
            {{ props.row.customer.name }} <br>
            {{ props.row.customer.phone }}
          </div>
          <div slot="total" slot-scope="props">
            {{ currency + formatNumber(props.row.total, 2) }}
          </div>
          <div slot="created_at" slot-scope="props">
            {{ moment(props.row.created_at).format('MMMM Do YYYY, h:mm:ss a') }}
          </div>
          <div slot="updated_at" slot-scope="props">
            <div v-if="props.row.order_status === 'Delivered'">
              {{ moment(props.row.updated_at).format('MMMM Do YYYY, h:mm:ss a') }}
            </div>
          </div>
          <div slot="action" slot-scope="props">
            <a class="btn btn-primary" @click="order=props.row; page.option='order_details'"><i class="el-icon-tickets" /></a>
            <a v-if="props.row.bulk_order_cancellation === 1" class="btn btn-danger" @click="undoCancellation(props.index, props.row);">Reverse Cancellation</a>
            <!-- <el-dropdown class="avatar-container right-menu-item hover-effect" trigger="click">
              <div class="avatar-wrapper" style="color: brown">
                <label style="cursor:pointer"><i class="el-icon-more-outline" /></label>
              </div>
              <el-dropdown-menu slot="dropdown" style="padding: 10px;">
                <el-dropdown-item v-if="props.row.order_status === 'pending' && checkPermission(['approve order'])">
                  <a @click="approveOrder(props.index, props.row);">Approve</a>
                </el-dropdown-item>
                <el-dropdown-item v-if="props.row.order_status === 'approved' && checkPermission(['approve order', 'deliver order'])" divided>
                  <a @click="deliverOrder(props.index, props.row);">Delivered</a>
                </el-dropdown-item>
                <el-dropdown-item v-if="props.row.order_status === 'pending' && checkPermission(['cancel order'])" divided>
                  <a @click="cancelOrder(props.index, props.row);">Cancel</a>
                </el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown> -->
          </div>

        </v-client-table>
        <el-row :gutter="20">
          <pagination
            v-show="total > 0"
            :total="total"
            :page.sync="form.page"
            :limit.sync="form.limit"
            @pagination="getOrders"
          />
        </el-row>

      </div>

    </div>
    <div v-if="page.option==='order_details'">
      <a class="btn btn-danger no-print" @click="page.option='list'; getOrders();">Go Back</a>
      <a class="btn btn-primary no-print" @click="doPrint();">Print</a>
      <order-details :order="order" :page="page" :can-update="true" />
    </div>
  </div>
</template>
<script>
import moment from 'moment';
import { formatNumber } from '@/utils/index';
import Pagination from '@/components/Pagination';
import checkPermission from '@/utils/permission';
import checkRole from '@/utils/role';
import OrderDetails from './Details';
import Resource from '@/api/resource';
// import Vue from 'vue';
// const necessaryParams = new Resource('fetch-necessary-params');
const fetchOrders = new Resource('order/general');
const approveOrderResource = new Resource('order/general/approve');
const deliverOrderResource = new Resource('order/general/deliver');
const cancelOrderResource = new Resource('order/general/cancel');
// const deleteItemInStock = new Resource('stock/items-in-stock/delete');
export default {
  components: { Pagination, OrderDetails },
  data() {
    return {
      currency: '₦',
      locations: [{ id: 'all', name: 'All Locations' }],
      orders: [],
      order_statuses: [],
      columns: ['action', 'order_number', 'customer.name', 'total', 'created_at', 'order_status', 'payment_status', 'updated_at'],

      options: {
        headings: {
          'customer.name': 'Customer',
          order_number: 'Order Number',
          amount: 'Amount',
          created_at: 'Date',
          order_status: 'Status',
          payment_status: 'Payment',
          updated_at: 'Delivery Date',

          // id: 'S/N',
        },
        filterByColumn: true,
        perPage: 10,
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: [],
        filterable: [],
        // sortable: ['order_number', 'customer.name', 'created_at', 'order_status', 'payment_status'],
        // filterable: ['order_number', 'customer.name', 'created_at', 'order_status', 'payment_status'],
      },
      page: {
        option: 'list',
      },
      // params: [],
      form: {
        page: 1,
        limit: 10,
        location_index: '',
        location_id: '',
        status: 'Pending',
      },
      total: 0,
      in_location: '',
      order: {},
      selected_row_index: '',
      load: false,
      search_query: '',

    };
  },
  computed: {
    params() {
      return this.$store.getters.params;
    },
  },
  created() {
    // this.fetchNecessaryParams();
    this.getOrders();
  },
  beforeDestroy() {

  },
  methods: {
    moment,
    checkPermission,
    checkRole,
    formatNumber,
    // fetchNecessaryParams() {
    //   const app = this;
    //   necessaryParams.list()
    //     .then(response => {
    //       app.params = response.params;
    //       // app.locations = app.locations.concat(response.params.locations);
    //       // app.order_statuses = response.params.order_statuses;
    //       // if (app.locations.length > 0) {
    //       //   app.form.location_id = app.locations[0];
    //       //   app.form.location_index = 0;
    //       //   app.getOrders();
    //       // }
    //     });
    // },
    getOrders() {
      const app = this;
      const { limit, page } = this.form;
      this.options.perPage = limit;
      this.load = true;

      const param = app.form;
      fetchOrders.list(param)
        .then(response => {
          this.orders = response.orders.data;
          this.orders.forEach((element, index) => {
            element['index'] = (page - 1) * limit + index + 1;
          });
          this.total = response.orders.total;
          //  app.in_location = 'in ' + app.locations[param.location_index].name;
          this.load = false;
        })
        .catch(error => {
          this.load = false;
          console.log(error.message);
        });
    },
    searchOrderNumber() {
      const app = this;
      const param = { search_query: app.search_query };
      app.load = true;
      const searchOrderNo = new Resource('order/general/search-order');
      searchOrderNo
        .list(param)
        .then((response) => {
          this.orders = response.orders;
          this.load = false;
        })
        .catch((error) => {
          console.log(error);
          this.load = false;
        });
    },
    deliverOrder(index, order){
      const app = this;
      const param = { status: 'delivered' };
      deliverOrderResource.update(order.id, param)
        .then(response => {
          app.orders.splice(index - 1, 1);
        });
    },
    approveOrder(index, order){
      const app = this;
      const param = { status: 'approved' };
      approveOrderResource.update(order.id, param)
        .then(response => {
          app.orders.splice(index - 1, 1);
        });
    },
    cancelOrder(index, order){
      const app = this;
      const param = { status: 'cancelled' };
      cancelOrderResource.update(order.id, param)
        .then(response => {
          app.orders.splice(index - 1, 1);
        });
    },
    undoCancellation(index, order) {
      const app = this;
      if (confirm('Confirm Action')) {
        const param = { status: 'carp' };
        const reverseOrderResource = new Resource('order/general/reverse-cancellation');
        reverseOrderResource.update(order.id, param)
          .then(response => {
            app.orders.splice(index - 1, 1);
            app.$message('Reversal Completed');
          });
      }
    },
    doPrint() {
      window.print();
    },

  },
};
</script>
