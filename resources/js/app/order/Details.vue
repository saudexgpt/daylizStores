<template>
  <section v-if="order" class="invoice">
    <!-- title row -->
    <div class="row">
      <div class="col-xs-12 page-header" align="center">
        <img src="/svg/logo.jpg" alt="Company Logo" width="100">
        <!-- <span><label>{{ companyName }}</label></span> -->
      </div>
      <!-- /.col -->
    </div>
    <!-- info row -->
    <div class="row invoice-info">
      <div class="col-sm-4 invoice-col">
        <label>Customer Details</label>
        <address>
          <h4>{{ order.customer.name.toUpperCase() }}</h4><br>
          <label>Email:   </label>&nbsp;{{ order.customer.email }}<br>
          <label>Phone:   </label>&nbsp;{{ order.customer.phone }}<br>
          <label>Nearest: </label>&nbsp;{{ order.nearest_bustop }}<br>
          <label>Address: </label>&nbsp;{{ order.address }}
        </address>
      </div>
      <!-- /.col -->
      <div class="col-sm-4 invoice-col">
        <!-- <label>Concerned Location</label>
            <address>
              <strong>{{ order.location.name.toUpperCase() }}</strong><br>
            </address> -->
      </div>
      <!-- /.col -->
      <div class="col-sm-4 invoice-col">
        <!-- <label>Order Details</label><br> -->
        <h3>Order No.: {{ order.order_number }}</h3><br>
        <label>Date:</label> {{ moment(order.created_at).format('MMMM Do YYYY, h:mm:ss a') }}<br>
        <label>Pickup Area: </label> {{ order.location }}
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->

    <!-- Table row -->
    <div class="row">
      <div class="col-xs-12 table-responsive">
        <legend>Ordered Product</legend>
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Product</th>
              <th>Quantity</th>
              <!-- <th>Rate</th> -->
              <!-- <th>Tax</th> -->
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(order_item, index) in order.order_items" :key="index">
              <td>{{ order_item.product_name }}</td>
              <td>{{ order_item.quantity }}</td>
              <!-- <td>{{ order_item.item.price.amount }}</td> -->
              <!-- <td>{{ (order_item.tax * 100).toFixed(2) }}%</td> -->
              <td align="right">{{ currency + formatNumber(order_item.total, 2) }}</td>
            </tr>
            <tr>
              <td colspan="2">
                <label>Shipping Cost:</label>
                [{{ order.location }}]
              </td>
              <td align="right">
                {{ '₦' + formatNumber(order.delivery_cost, 2) }}
              </td>
            </tr>
            <tr>
              <td colspan="2" align="right"><label>Grand Total</label></td>
              <td align="right"><label style="color: green">{{ currency + formatNumber(order.total, 2).toLocaleString() }}</label></td>
            </tr>
            <tr>
              <td colspan="3" align="right"><label>In Words: {{ inWords(order.total).toUpperCase() + ' NAIRA ONLY' }}</label></td>
            </tr>
            <tr v-if="!canUpdate">
              <td colspan="3" align="right">
                <label>Payment Status: {{ order.payment_status }}</label>
              </td>
            </tr>
            <tr v-if="form.status !== 'Cancelled' && order.payment_status === 'pending' && canUpdate">
              <td colspan="3" align="right">
                <a
                  class="btn btn-success"
                  @click="form.payment_status = 'paid'; changeOrderStatus()"
                > <i class="el-icon-printer" /> Mark as Paid</a>
                <el-alert
                  :closable="false"
                  type="error"
                >This should be done only when customer has paid in full
                </el-alert>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->

    <div class="row">
      <!-- accepted payments column -->
      <div class="col-xs-12 col-sm-6 col-md-6">
        <label>CURRENT ORDER DELIVERY STATUS</label>
        <div v-if="order.order_status === 'Pending'">
          <img src="/images/pending.png" alt="Pending" width="150">
          <br>
          <label>Order delivery is pending</label>
        </div>
        <div v-else-if="order.order_status === 'On Transit'">
          <img src="/images/transit.png" alt="Transition" width="150">
          <br>
          <label>Order is currently on transit for delivery</label>
        </div>
        <div v-else-if="order.order_status === 'Delivered'">
          <img src="/images/delivered.png" alt="Delivered" width="150">
          <br>
          <label>Order is delivered</label>
        </div>
        <div v-else-if="order.order_status === 'Cancelled'">
          <img src="/images/cancelled.png" alt="Cancelled" width="150">
          <br>
          <label>Order is cancelled</label>
        </div>
      </div>
      <!-- /.col -->
      <div v-if="canUpdate" class="col-xs-12 col-sm-6 col-md-6">
        <div v-if="order.payment_status === 'paid' && order.order_status === 'Pending'">
          <a
            class="btn btn-primary"
            @click="form.status = 'On Transit'; changeOrderStatus()"
          > <i class="el-icon-printer" /> Send to Customer</a>
          <el-alert
            :closable="false"
            type="error"
          >This should be done only when customer has paid in full.
          </el-alert>
        </div>
        <div v-else-if="order.payment_status === 'paid' && order.order_status === 'On Transit'">
          <a
            class="btn btn-success"
            @click="form.status = 'Delivered'; changeOrderStatus()"
          >Click to Mark Goods as Delivered</a>
          <el-alert
            :closable="false"
            type="error"
          >This should be done only when order have been delivered successfully to the customer
          </el-alert>
        </div>
        <div v-if="order.order_status === 'Pending' || order.order_status === 'On Transit'">
          <a
            class="btn btn-danger"
            @click="form.status = 'Cancelled'; changeOrderStatus()"
          >Click to Cancel Order</a>
          <el-alert
            :closable="false"
            type="error"
          >This should be done only when order is no longer needed by the customer
          </el-alert>
        </div>
        <div v-if="order.order_status === 'Delivered'">
          <div v-if="order.payment_status === 'paid'">
            <h1>Paid</h1>
          </div>
          <div v-else>
            <h1>Payment Pending</h1>
          </div>
        </div>
      </div>
      <div v-else-if="order.order_status === 'Delivered'" class="col-xs-12 col-sm-6 col-md-6">
        <h4>Kindly rate our product</h4>
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Product</th>
              <th>Rate</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(order_item, index) in order.order_items" :key="index">
              <td>{{ order_item.item.name }}</td>
              <td>
                <el-rate
                  v-model="order_item.star"
                  @change="rateProduct($event, order_item.item_id, order.user_id, 'star')"
                /><br>
                <el-input v-model="order_item.comment" type="textarea" @blur="rateProduct($event.target.value, order_item.item_id, order.user_id, 'comment')" />
              </td>
            </tr>
            <tr>
              <td align="right" colspan="2"><el-button type="success" round @click="appreciate">Submit</el-button></td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </section>
</template>

<script>
import moment from 'moment';
import { formatNumber } from '@/utils/index';
import defaultSettings from '@/settings';
import Resource from '@/api/resource';
const { companyName } = defaultSettings;
const orderResource = new Resource('orders');

export default {
  props: {
    order: {
      type: Object,
      default: () => ({}),
    },
    page: {
      type: Object,
      default: () => ({
        option: 'order_details',
      }),
    },
    canUpdate: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      currency: '₦',
      companyName,
      activeActivity: 'first',
      updating: false,
      form: {
        status: 'Pending',
        payment_status: 'pending',
      },
    };
  },
  created() {
    this.form.status = this.order.order_status;
    this.form.payment_status = this.order.payment_status;
  },
  methods: {
    moment,
    formatNumber,
    onSubmit() {
      this.updating = true;
      orderResource
        .update(this.order.id, this.order)
        .then(response => {
          this.updating = false;
          this.$message({
            message: 'User information has been updated successfully',
            type: 'success',
            duration: 5 * 1000,
          });
        })
        .catch(error => {
          console.log(error);
          this.updating = false;
        });
    },
    inWords(n) {
      var string = n.toString(), units, tens, scales, start, end, chunks, chunksLen, chunk, ints, i, word, words, and = 'and';

      /* Remove spaces and commas */
      string = string.replace(/[, ]/g, '');

      /* Is number zero? */
      if (parseInt(string) === 0) {
        return 'zero';
      }

      /* Array of units as words */
      units = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];

      /* Array of tens as words */
      tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

      /* Array of scales as words */
      scales = ['', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion', 'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quatttuor-decillion', 'quindecillion', 'sexdecillion', 'septen-decillion', 'octodecillion', 'novemdecillion', 'vigintillion', 'centillion'];

      /* Split user argument into 3 digit chunks from right to left */
      start = string.length;
      chunks = [];
      while (start > 0) {
        end = start;
        chunks.push(string.slice((start = Math.max(0, start - 3)), end));
      }

      /* Check if function has enough scale words to be able to stringify the user argument */
      chunksLen = chunks.length;
      if (chunksLen > scales.length) {
        return '';
      }

      /* Stringify each integer in each chunk */
      words = [];
      for (i = 0; i < chunksLen; i++) {
        chunk = parseInt(chunks[i]);

        if (chunk) {
          /* Split chunk into array of individual integers */
          ints = chunks[i].split('').reverse().map(parseFloat);

          /* If tens integer is 1, i.e. 10, then add 10 to units integer */
          if (ints[1] === 1) {
            ints[0] += 10;
          }

          /* Add scale word if chunk is not zero and array item exists */
          if ((word = scales[i])) {
            words.push(word);
          }

          /* Add unit word if array item exists */
          if ((word = units[ ints[0] ])) {
            words.push(word);
          }

          /* Add tens word if array item exists */
          if ((word = tens[ ints[1] ])) {
            words.push(word);
          }

          /* Add 'and' string after units or tens integer if: */
          if (ints[0] || ints[1]) {
            /* Chunk has a hundreds integer or chunk is the first of multiple chunks */
            if (ints[2] || !i && chunksLen) {
              words.push(and);
            }
          }

          /* Add hundreds word if array item exists */
          if ((word = units[ ints[2] ])) {
            words.push(word + ' hundred');
          }
        }
      }

      return words.reverse().join(' ');
    },
    changeOrderStatus(){
      const app = this;
      const changeOrderStatusResource = new Resource('order/general/change-status');
      var param = app.order;
      const message = 'Do you want to continue with this action?';
      if (confirm(message)) {
        param.status = app.form.status;
        param.payment_status = app.form.payment_status;
        changeOrderStatusResource.update(param.id, param)
          .then(response => {
            app.order.order_status = app.form.status;
            // if (app.form.status === 'On Transit') {
            //   app.print_waybill = true;
            // }
          });
      }
    },
    rateProduct(value, itemId, userId, field) {
      // const app = this;
      const changeOrderStatusResource = new Resource('give-product-review');
      var param = { value, item_id: itemId, user_id: userId, field };
      changeOrderStatusResource.store(param)
        .then(() => {

        });
    },
    appreciate() {
      const app = this;
      app.$message('Thank you for your feedback');
    },
  },
};
</script>

<style lang="scss" scoped>
.order-activity {
  .order-block {
    .ordername, .description {
      display: block;
      margin-left: 50px;
      padding: 2px 0;
    }
    img {
      width: 40px;
      height: 40px;
      float: left;
    }
    :after {
      clear: both;
    }
    .img-circle {
      border-radius: 50%;
      border: 2px solid #d2d6de;
      padding: 2px;
    }
    span {
      font-weight: 500;
      font-size: 12px;
    }
  }
  .post {
    font-size: 14px;
    border-bottom: 1px solid #d2d6de;
    margin-bottom: 15px;
    padding-bottom: 15px;
    color: #666;
    .image {
      width: 100%;
    }
    .order-images {
      padding-top: 20px;
    }
  }
  .list-inline {
    padding-left: 0;
    margin-left: -5px;
    list-style: none;
    li {
      display: inline-block;
      padding-right: 5px;
      padding-left: 5px;
      font-size: 13px;
    }
    .link-black {
      &:hover, &:focus {
        color: #999;
      }
    }
  }
  .el-carousel__item h3 {
    color: #475669;
    font-size: 14px;
    opacity: 0.75;
    line-height: 200px;
    margin: 0;
  }

  .el-carousel__item:nth-child(2n) {
    background-color: #99a9bf;
  }

  .el-carousel__item:nth-child(2n+1) {
    background-color: #d3dce6;
  }
}
</style>
