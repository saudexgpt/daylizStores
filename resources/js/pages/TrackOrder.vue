<template>
  <div v-loading="loading">
    <div class="text-center wow fadeInUp" data-wow-delay="0.1s" style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInUp;">
      <h1 class="section-title ff-secondary text-center text-primary-custom fw-normal">Track My Order</h1>
    </div>
    <el-row
      v-if="page.option=='order_details'"
      :gutter="20"
    >
      <el-col
        :lg="24"
        :md="24"
        :sm="24"
        :xs="24"
      >
        <a class="btn btn-danger no-print" @click="page.option='list';">Go Back</a>
        <order-details :order="order" />
      </el-col>
    </el-row>
    <el-row v-else :gutter="20">
      <el-col
        v-if="userData.id === null"
        :lg="24"
        :md="24"
        :sm="24"
        :xs="24"
      >
        <el-card>
          <span slot="header">Kindly fill the form below to continue</span>
          <el-row :gutter="10">
            <el-col
              :lg="10"
              :md="10"
              :sm="10"
              :xs="24"
            >

              <mdb-input
                v-model="trackOrderForm.username"
                outline
                name="username"
                type="text"
                auto-complete="off"
                label="Email OR Phone *"
                required="required"
              />
            </el-col>
            <el-col
              :lg="10"
              :md="10"
              :sm="10"
              :xs="24"
            >

              <mdb-input
                v-model="trackOrderForm.order_number"
                outline
                name="order_number"
                type="text"
                auto-complete="off"
                label="Order Number *"
                required="required"
              />
            </el-col>
            <el-col
              :lg="4"
              :md="4"
              :sm="4"
              :xs="24"
            >
              <br>
              <el-button type="primary" size="lg" round @click="submitOrder">
                Track
              </el-button>
            </el-col>

          </el-row>
        </el-card>
      </el-col>
      <el-col
        v-else
        :lg="24"
        :md="24"
        :sm="24"
        :xs="24"
      >
        <el-card>
          <v-client-table v-model="orders" :columns="columns" :options="options">
            <div slot="total" slot-scope="props">
              {{ currency + formatNumber(props.row.total, 2) }}
            </div>
            <div slot="created_at" slot-scope="props">
              {{ moment(props.row.created_at).format('MMMM Do YYYY, h:mm:ss a') }}
            </div>
            <div slot="action" slot-scope="props">
              <a class="btn btn-primary" @click="order=props.row; page.option='order_details'"><i class="el-icon-tickets" /> View</a>
            </div>

          </v-client-table>
          <el-row :gutter="20">
            <pagination
              v-show="total > 0"
              :total="total"
              :page.sync="form.page"
              :limit.sync="form.limit"
              @pagination="myOrders"
            />
          </el-row>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>
<script>
import 'mdbvue/lib/css/mdb.min.css';
import moment from 'moment';
import Pagination from '@/components/Pagination';
import Resource from '@/api/resource';
import OrderDetails from '@/app/order/Details';
import { formatNumber } from '@/utils/index';
export default {
  name: 'Trackorder',
  components: {
    OrderDetails,
    Pagination,
  },
  data() {
    return {
      currency: '₦',
      orders: [],
      columns: ['action', 'order_number', 'total', 'created_at'],

      options: {
        headings: {
          order_number: 'Order Number',
          amount: 'Amount',
          created_at: 'Date',

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
      selectedItem: null,
      loading: false,
      trackOrderForm: {
        order_number: '',
        username: '',
      },
      form: {
        page: 1,
        limit: 10,
      },
      total: 0,
      order: null,
    };
  },
  computed: {
    userData() {
      return this.$store.getters.userData;
    },
  },
  created() {
    this.setForm();
    if (this.userData.id !== null) {
      this.myOrders();
    }
  },
  methods: {
    moment,
    formatNumber,
    setForm() {
      const app = this;
      app.trackOrderForm.username = app.userData.email;
    },
    submitOrder() {
      const app = this;
      app.order = null;
      const form = app.trackOrderForm;
      if (form.phone === '' || form.order_number === '') {
        alert('Kindly fill all the required fields');
        return false;
      }
      app.loading = true;
      const storeOrder = new Resource('order/search');
      storeOrder.store(form).then(response => {
        if (response.message === 'success') {
          app.order = response.order;
          app.page.option = 'order_details';
        } else {
          alert('DATA NOT FOUND');
        }
        app.loading = false;
      }).catch(err => {
        alert(err.response.message);
        app.loading = false;
      });
    },
    myOrders() {
      const app = this;
      const { limit, page } = this.form;
      this.options.perPage = limit;
      this.loading = true;

      const param = app.form;
      const myOrdersResource = new Resource('order/general/my-orders');
      myOrdersResource.list(param)
        .then(response => {
          this.orders = response.orders.data;
          this.orders.forEach((element, index) => {
            element['index'] = (page - 1) * limit + index + 1;
          });
          this.total = response.orders.total;
          //  app.in_location = 'in ' + app.locations[param.location_index].name;
          this.loading = false;
        })
        .catch(error => {
          this.loading = false;
          console.log(error.message);
        });
    },
  },
};
</script>
<style rel="stylesheet/scss" lang="scss">
.md-form label.active {
    font-size: 1.8rem;
  }
  .md-form .prefix {
    top: 0.25rem;
    font-size: 1.8rem;
  }
  .md-form.md-outline .prefix {
    position: absolute;
    top: 0.9rem;
    font-size: 1.9rem;
    -webkit-transition: color 0.2s;
    transition: color 0.2s;
  }
  .md-form.md-outline .form-control {
    padding: 1rem;
  }
</style>
