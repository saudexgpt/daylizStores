<template>
  <div class="app-container">
    <div class="text-center wow fadeInUp" data-wow-delay="0.1s" style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInUp;">
      <h1 class="section-title ff-secondary text-center text-primary-custom fw-normal">Check Out</h1>
      <el-alert type="error"><h4>Please note that delivery fees are dependent on your location and is not added to your charges</h4></el-alert>
    </div>
    <el-row :gutter="20">
      <div v-if="pendingOrder.cart_items.length > 0">

        <el-col
          :lg="8"
          :md="8"
          :sm="12"
          :xs="24"
        >
          <el-card v-if="userData.id === null">
            <span slot="header">Kindly fill the form below to continue</span>
            <div>
              <!-- <label for="">Full Name <code>*</code></label>
              <el-input v-model="checkOutForm.name" placeholder="Enter your full name" /> -->
              <mdb-input
                v-model="checkOutForm.name"
                outline
                name="name"
                type="text"
                auto-complete="off"
                label="Full Name *"
                required="required"
              />

              <mdb-input
                v-model="checkOutForm.email"
                outline
                far
                name="email"
                type="email"
                auto-complete="off"
                label="Email *"
                required="required"
              />
              <mdb-input
                v-model="checkOutForm.phone"
                outline
                name="phone"
                type="number"
                auto-complete="off"
                label="Phone *"
                required="required"
              />
              <mdb-input
                v-model="checkOutForm.address"
                outline
                name="address"
                type="textarea"
                auto-complete="off"
                label="Address (House No. and street name) *"
                required="required"
              />
              <mdb-input
                v-model="checkOutForm.nearest_bustop"
                outline
                name="nearest_bustop"
                type="text"
                auto-complete="off"
                label="Nearest Bustop"
                required="required"
              />
              <!-- <label for="">Phone Number <code>*</code></label>
              <el-input v-model="checkOutForm.phone" type="number" placeholder="Enter your phone number" /> -->
              <!-- <label for="">Delivery Address <code>*</code></label>
              <el-input v-model="checkOutForm.address" type="textarea" placeholder="Enter the address you want us to deliver your order" /> -->
              <mdb-input
                v-model="checkOutForm.notes"
                outline
                name="notes"
                type="textarea"
                auto-complete="off"
                placeholder="You can give extra note for this order"
              />
            </div>
          </el-card>
          <el-card v-else>
            <span slot="header"><label>Continue as {{ userData.name }}</label></span>
            <div>
              <!-- <label for="">Full Name <code>*</code></label>
              <el-input v-model="checkOutForm.name" placeholder="Enter your full name" /> -->
              <mdb-input
                v-model="checkOutForm.name"
                outline
                name="name"
                type="text"
                auto-complete="off"
                label="Full Name *"
                required="required"
                disabled
              />

              <mdb-input
                v-model="checkOutForm.email"
                outline
                far
                name="email"
                type="email"
                auto-complete="off"
                label="Email *"
                required="required"
                disabled
              />
              <mdb-input
                v-model="checkOutForm.phone"
                outline
                name="phone"
                type="number"
                auto-complete="off"
                label="Phone *"
                required="required"
                disabled
              />
              <mdb-input
                v-model="checkOutForm.address"
                outline
                name="address"
                type="textarea"
                auto-complete="off"
                label="Address (House No. and street name) *"
                required="required"
              />
              <mdb-input
                v-model="checkOutForm.nearest_bustop"
                outline
                name="nearest_bustop"
                type="text"
                auto-complete="off"
                label="Nearest Bustop"
                required="required"
              />
              <!-- <label for="">Phone Number <code>*</code></label>
              <el-input v-model="checkOutForm.phone" type="number" placeholder="Enter your phone number" /> -->
              <!-- <label for="">Delivery Address <code>*</code></label>
              <el-input v-model="checkOutForm.address" type="textarea" placeholder="Enter the address you want us to deliver your order" /> -->
              <mdb-input
                v-model="checkOutForm.notes"
                outline
                name="notes"
                type="textarea"
                auto-complete="off"
                placeholder="You can give extra note for this order"
              />
            </div>
          </el-card>
        </el-col>
        <el-col
          :lg="16"
          :md="16"
          :sm="12"
          :xs="24"
        >
          <el-card v-loading="loading">
            <span slot="header">Order List</span>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>QTY</th>
                  <th>Rate</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in pendingOrder.cart_items" :key="index">
                  <td>
                    <img :src="item.media[0].link" width="100"><br>
                    <h4>{{ `${item.name} - ${item.selectedColor} - ${item.selectedSize}` }}</h4>
                    <!-- <div v-if="item.selected_color">

                      Color: <strong :style="`background: ${item.selected_color}; padding: 5px`">{{ item.selected_color }}</strong>
                    </div>
                    <strong>{{ (item.selected_size) ? `Size: ${item.selected_size}` : '' }}</strong> -->
                  </td>
                  <td>
                    {{ item.quantity }} {{ item.package_type }}
                  </td>
                  <td align="right">
                    <strong>{{ '₦' + formatNumber(item.rate, 2) }}</strong>
                  </td>
                  <td align="right">
                    <strong>{{ '₦' + formatNumber(parseFloat(item.rate * item.quantity), 2) }}</strong>
                  </td>
                </tr>
                <tr>
                  <td colspan="4">
                    <h4>Delivery Mode</h4>
                    <!-- <el-select
                      v-model="selected_location"
                      value-key="id"
                      filterable
                      style="width: 100% !important;"
                      @input="addDeliveryCost()"
                    > -->
                    <el-select
                      v-model="checkOutForm.location_id"
                      value-key="id"
                      filterable
                      style="width: 100% !important;"
                    >
                      <el-option value="" label="Pick Outlet Location" disabled />
                      <el-option
                        v-for="(location, loc_index) in locations"
                        :key="loc_index"
                        :value="location.id"
                        :label="location.name"
                      >
                        <span> {{ location.name }}</span>
                        <!-- <span class="pull-right" style="color: rgb(138, 34, 34); font-weight: 600;">&nbsp;{{ '₦' + formatNumber(location.cost, 2) }}</span> -->
                      </el-option>
                    </el-select>
                  </td>
                  <!-- <td align="right">
                    {{ '₦' + formatNumber(deliveryCost, 2) }}
                  </td> -->
                </tr>
                <tr>
                  <td colspan="3" align="right"><h4>Grand Total</h4></td>
                  <td align="right"><h4>{{ '₦' + formatNumber(pendingOrder.amount, 2) }}</h4></td>
                </tr>
                <tr>
                  <td colspan="4" align="right">
                    <button class="btn btn-success" @click="submitOrder">
                      Submit Order
                    </button>
                  </td>
                </tr>

              </tbody>
            </table>
          </el-card>
        </el-col>
      </div>
      <div v-else>
        <el-col :xs="24">
          <h4> You do not have any item in your cart</h4>
        </el-col>
      </div>
    </el-row>
  </div>
</template>
<script>
import 'mdbvue/lib/css/mdb.min.css';
import { formatNumber } from '@/utils/index';
import Resource from '@/api/resource';
export default {
  name: 'CheckOut',
  data() {
    return {
      selectedItem: null,
      loading: false,
      checkOutForm: {
        name: '',
        phone: '',
        email: '',
        nearest_bustop: '',
        address: '',
        notes: '',
        location_id: 1,
      },
      locations: [],
      selected_location: {},
      amount: '',
      deliveryCost: 0.00,
    };
  },
  computed: {
    pendingOrder() {
      return this.$store.getters.pendingOrder;
    },
    userData() {
      return this.$store.getters.userData;
    },
  },
  created() {
    this.$store.dispatch('order/loadOfflineData');
    this.fetchLocations();
    this.setForm();
  },
  methods: {
    formatNumber,
    setForm() {
      const app = this;
      app.checkOutForm.name = app.userData.name;
      app.checkOutForm.email = app.userData.email;
      app.checkOutForm.phone = app.userData.phone;
      app.checkOutForm.address = app.userData.address;
      app.checkOutForm.nearest_bustop = app.userData.nearest_bustop;
    },
    addDeliveryCost() {
      const app = this;
      const location = app.selected_location;
      app.checkOutForm.location_id = location.id;
      app.amount = parseFloat(app.pendingOrder.amount + location.cost);
      app.deliveryCost = location.cost;
    },
    fetchLocations(){
      const app = this;
      app.load = true;
      const locationResource = new Resource('fetch-location');
      locationResource.list().then(response => {
        app.load = false;
        app.locations = response.locations;
        app.amount = app.pendingOrder.amount;
        app.checkOutForm.location_id = 1;
      }).catch(() => {
        app.load = false;
      });
    },
    submitOrder() {
      const app = this;
      const form = app.checkOutForm;
      form.cart_items = app.pendingOrder.cart_items;
      form.amount = app.pendingOrder.amount;
      form.total = app.amount;
      form.delivery_cost = app.deliveryCost;
      if (form.name === '' || form.email === '' || form.phone === '' || form.address === '') {
        alert('Kindly fill all the required fields');
        return false;
      }

      const storeOrder = new Resource('order/store');
      app.loading = true;
      storeOrder.store(form).then(response => {
        if (response.message === 'success') {
          app.$alert('Your order is placed successfully. Order Number is: ' + response.order_no + ' You can use it to track this order');
          this.$alert('A message is sent to your email <br>Order Number is: ' + response.order_no + '<br> You can use it to track this order', 'Order Placed Successfully', {
            dangerouslyUseHTMLString: true,
          });
          app.loading = false;
          const pendingOrder = {
            amount: 0,
            cart_items: [],
          };
          app.$store.dispatch('order/setCartItems', []);
          app.$store.dispatch('order/setPendingOrder', pendingOrder);
        }
      }).catch(err => {
        console.log(err);
        alert('An error occured');
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
