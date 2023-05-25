<template>
  <div v-loading="loading">
    <div class="text-center wow fadeInUp" data-wow-delay="0.1s" style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInUp;">
      <h1 class="section-title ff-secondary text-center text-primary-custom fw-normal">Track My Order</h1>
    </div>
    <el-row :gutter="20">
      <el-col
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
        v-if="order !== null"
        :lg="24"
        :md="24"
        :sm="24"
        :xs="24"
      >
        <order-details :order="order" />
        <!-- <el-card>
          <h4 align="center">Order List for {{ order.order_number }}</h4>
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Food</th>
                <th>QTY</th>
                <th>Rate</th>
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(order_item, index) in order.order_items" :key="index">
                <td>
                  <img :src="order_item.item.picture" width="50"><br>
                  <strong>{{ order_item.item.name }}</strong>
                </td>
                <td>
                  {{ order_item.quantity }} {{ order_item.item.package_type }}
                </td>
                <td>
                  <strong>{{ '₦' + order_item.price }}</strong>
                </td>
                <td>
                  <strong>{{ '₦' + order_item.total }}</strong>
                </td>
              </tr>
              <tr>
                <td colspan="3" align="right"><h3>Total</h3></td>
                <td><h3>{{ '₦' + order.total }}</h3></td>
              </tr>
              <tr>
                <td colspan="3" align="right"><h4>Payment</h4></td>
                <td><h4>{{ order.payment_status }}</h4></td>
              </tr>
              <tr>
                <td colspan="3" align="right"><h4>Delivery</h4></td>
                <td><h4>{{ order.order_status }}</h4></td>
              </tr>
            </tbody>
          </table>
        </el-card> -->
      </el-col>
    </el-row>
  </div>
</template>
<script>
import 'mdbvue/lib/css/mdb.min.css';
import Resource from '@/api/resource';
import OrderDetails from '@/app/order/Details';
export default {
  name: 'Trackorder',
  components: {
    OrderDetails,
  },
  data() {
    return {
      selectedItem: null,
      loading: false,
      trackOrderForm: {
        order_number: '',
        username: '',
      },
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
  },
  methods: {
    setForm() {
      const app = this;
      app.trackOrderForm.username = app.userData.email;
    },
    submitOrder() {
      const app = this;
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
        } else {
          alert('DATA NOT FOUND');
        }
        app.loading = false;
      }).catch(err => {
        alert(err.response.message);
        app.loading = false;
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
