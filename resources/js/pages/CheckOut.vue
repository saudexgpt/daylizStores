<template>
  <div v-loading="loading" class="app-container">
    <div class="text-center wow fadeInUp" data-wow-delay="0.1s" style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInUp;">
      <h1 class="section-title ff-secondary text-center text-primary-custom fw-normal">Checkout</h1>
    </div>
    <div v-if="userData.id === null" class="callout callout-info">Returning Customer? <router-link :to="{ path: '/login?redirect=/product/check-out'}">Click here to login</router-link> </div>
    <el-row v-if="params.can_make_order" :gutter="20">
      <div v-if="pendingOrder.cart_items.length > 0">

        <el-col
          :lg="12"
          :md="12"
          :sm="12"
          :xs="24"
        >
          <el-card>
            <div v-if="userData.id === null">
              <span slot="header">Kindly fill the form below to continue</span>
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
            </div>
            <div v-else>
              <span slot="header"><label>Continue as {{ userData.name }}</label></span>
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
            </div>
            <div>
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
              <h4>Delivery Location</h4>

              <el-alert type="error">Please note that delivery fees are dependent on your location and will be charged separately</el-alert>
              <br>
              <!-- <el-select
                      v-model="selected_location"
                      value-key="id"
                      filterable
                      style="width: 100% !important;"
                      @input="addDeliveryCost()"
                    > -->
              <el-cascader
                v-model="checkOutForm.location"
                placeholder="Pick Delivery Location"
                :options="options"
                :props="{ expandTrigger: 'hover' }"
                filterable
                :filter-method="customFilter"
                style="width: 100% !important;"
              />
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
          :lg="12"
          :md="12"
          :sm="12"
          :xs="24"
        >
          <el-card>
            <span slot="header">Order List</span>
            <h4 align="center">Kindly note that we have NO PAYMENT REFUND POLICY</h4>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in pendingOrder.cart_items" :key="index">
                  <td>
                    <img v-if="item.media.length > 0" :src="item.media[0].link" width="100">
                    <img v-else src="/images/no-image.jpeg" width="100"><br>
                    <h4>{{ `${item.name}` }}</h4>
                    <label> {{ item.quantity }}pieces @{{ '₦' + formatNumber(item.rate, 2) }}</label>
                    <!-- <div v-if="item.selected_color">

                      Color: <strong :style="`background: ${item.selected_color}; padding: 5px`">{{ item.selected_color }}</strong>
                    </div>
                    <strong>{{ (item.selected_size) ? `Size: ${item.selected_size}` : '' }}</strong> -->
                  </td>
                  <td align="right">
                    <strong>{{ '₦' + formatNumber(parseFloat(item.rate * item.quantity), 2) }}</strong>
                  </td>
                </tr>
                <tr>
                  <td align="right"><h4>Total</h4></td>
                  <td align="right"><h4>{{ '₦' + formatNumber(pendingOrder.amount, 2) }}</h4></td>
                </tr>
                <tr>
                  <td colspan="2">
                    <div v-if="params">
                      <el-alert type="error">Make your payment directly into any of our bank accounts stated below. Please use your Order Number as the payment reference. Your order will not be shipped until payment is made and confirmed.</el-alert>
                      <h3 class="section-title-footer ff-secondary text-start text-dark fw-normal mb-4">Pay To</h3>
                      <span v-html="params.account_details" />
                    </div>
                  </td>
                </tr>
                <tr>
                  <td colspan="2">
                    <div><el-checkbox v-model="termsAgreed" /> I have read and agreed to the website <label><a @click="showTermsAndConditions = true">Terms and Conditions</a></label></div>
                    <button class="btn btn-success btn-lg" @click="submitOrder">
                      Submit Order to generate Order Number
                    </button>
                  </td>
                </tr>

              </tbody>
            </table>
          </el-card>
        </el-col>
        <el-dialog
          title="Terms and Conditions"
          :visible.sync="showTermsAndConditions"
        >
          <div v-if="params">
            <el-input v-model="params.terms_and_conditions" type="textarea" readonly resize="vertical" :rows="20" />
          </div>
        </el-dialog>
        <el-dialog
          :visible="showOrderSuccessMessage"
          title="Order Placed Successfully"
          :close-on-click-modal="false"
          @close="showOrderSuccessMessage = false"
        >
          <div>
            <div style="border: dashed 2px #47cf2c; padding: 10px; color: #47cf2c"><label>Thank you. We have received your order</label></div>
            <hr>
            <label>
              Order Number: <h3>{{ orderDetails.order_number }}</h3>
            </label>
            <hr>
            <label>
              Order Date: {{ moment(orderDetails.created_at).format('ll') }}
            </label>
            <hr>
            <label>
              Total Amount: {{ '₦' + formatNumber(orderDetails.total, 2) }}
            </label>
            <hr>
            <label>
              Payment Method: {{ orderDetails.payment_method }}
            </label>
            <hr>
            <div>Please use your <label>Order Number</label> as your payment reference and for Tracking</div>
            <hr>
            <button class="btn btn-primary" @click="$router.push({ path: '/track/order' })">Track Order Here</button>
          </div>
        </el-dialog>
      </div>
      <div v-else align="center">
        <el-col :xs="24">
          <h4> You do not have any item in your cart</h4>
          <button class="btn btn-primary" @click="$router.push({ path: '/product/list' })">Shop Now</button>
        </el-col>
      </div>
    </el-row>
    <div v-else>
      <img src="/images/lock.png">
      <h4>Sorry!!! We are not receiving orders for now. Please check back later.</h4>
    </div>
  </div>
</template>
<script>
import moment from 'moment';
import 'mdbvue/lib/css/mdb.min.css';
import { formatNumber } from '@/utils/index';
import Resource from '@/api/resource';
export default {
  name: 'CheckOut',
  data() {
    return {
      showTermsAndConditions: false,
      showOrderSuccessMessage: false,
      orderDetails: { order_number: '', created_at: '', total: '', payment_method: '' },
      termsAgreed: false,
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
        location: '',
      },
      options: [{
        value: 'Local Pickup',
        label: 'Local Pickup',
      }, {
        value: 'Lagos',
        label: 'Lagos Delivery',
      }, {
        value: 'Other States',
        label: 'Other States',
        children: [], // will be populated from backend
      }],
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
    params() {
      return this.$store.getters.params;
    },
  },
  created() {
    this.$store.dispatch('app/setNecessaryParams');
    this.$store.dispatch('order/loadOfflineData');
    this.fetchLocations();
    this.setForm();
    setTimeout(() => {
      this.setStates();
    }, 3000);
  },
  methods: {
    moment,
    formatNumber,
    setForm() {
      const app = this;
      app.checkOutForm.name = app.userData.name;
      app.checkOutForm.email = app.userData.email;
      app.checkOutForm.phone = app.userData.phone;
      app.checkOutForm.address = app.userData.address;
      app.checkOutForm.nearest_bustop = app.userData.nearest_bustop;
    },
    setStates() {
      const app = this;
      const states = app.params.states;
      const formattedStateArray = [];
      states.forEach(state => {
        if (state !== 'Lagos') {
          formattedStateArray.push({
            value: state,
            label: state,
          });
        }
      });
      app.options[2].children = formattedStateArray;
    },
    createFilter(queryString) {
      return (item) => {
        return (item.text.toLowerCase().indexOf(queryString.toLowerCase()) > -1);
      };
    },
    customFilter(node, keyword) {
      return (node.text.toLowerCase().indexOf(keyword.toLowerCase()) > -1);
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
        app.$alert('Kindly fill all the required fields (Name, Email, Phone, Address)');
        return false;
      }
      if (form.location === '') {
        app.$alert('Kindly specify your preferred delivery location');
        return false;
      }
      if (!app.termsAgreed) {
        app.$alert('You are required to read and agree to our Terms and Condition by clicking on the check box');
        return false;
      }

      const storeOrder = new Resource('order/store');
      app.$alert(`Kindly note that goods left unpicked after 14 days is at owner's risk. We will not be held responsible for it. Thank you for your patronage.`);
      app.loading = true;
      storeOrder.store(form).then(response => {
        app.loading = false;
        if (response.message === 'check_cart') {
          const details = response.details;
          let table_row = '';
          details.forEach(detail => {
            app.$store.dispatch('order/addItemToCart', detail.updated_item);
            table_row += `<tr>
                            <td>
                              ${detail.product}
                            </td>
                            <td>
                              ${detail.balance}
                            </td >
                          </tr>`;
          });
          this.$alert(`<div align="center">
            <div style="border: dashed 2px #be1712; padding: 10px; color: #be1712"><label>Sorry! You may need to review your cart content and click on Check Out again</label><p> Some products are fast moving and must have been sold out just before you checked out.</p><p>Affected products and their current stock balance are stated below</p></div>
            '<table class="table table-bordered">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Balance</th>
                </tr>
              </thead>
              <tbody>
                ${table_row}
              </tbody>
            </table>
          </div>`, 'Review Cart Content', {
            dangerouslyUseHTMLString: true,
          });
        } else {
          const orderDetails = response.order_details;
          this.$alert(`<div align="center">
            <div style="border: dashed 2px #47cf2c; padding: 10px; color: #47cf2c"><label>Thank you. We have received your order</label></div>
            <table class="table table-bordered">
              <tbody>
                <tr>
                  <td align="center">
                    <label>
                      Order Number: <h3>${orderDetails.order_number}</h3>
                    </label>
                  </td>
                </tr>
                <tr>
                  <td align="center">
                    <label>
                      Order Date: <br>${moment(orderDetails.created_at).format('ll')}
                    </label>
                  </td>
                </tr>
                <tr>
                  <td align="center">
                    <label>
                      Total Amount: <br>${'₦' + formatNumber(orderDetails.total, 2)}
                    </label>
                  </td>
                </tr>
              </tbody>
            </table>
            <div>Please use your <label>Order Number</label> as your payment reference and for Tracking</div>
            <p>Your order details was also sent to the email you provided</p>
          </div>`, 'Order Placed Successfully', {
            dangerouslyUseHTMLString: true,
          });
          const pendingOrder = {
            amount: 0,
            cart_items: [],
          };
          app.$store.dispatch('order/setCartItems', []);
          app.$store.dispatch('order/setPendingOrder', pendingOrder);
          app.$router.push({ path: '/track/order' });
        }
      }).catch(err => {
        console.log(err);
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
