<template>
  <div v-loading="load">
    <div v-if="selectedItem && load === false">
      <el-breadcrumb separator="|">
        <el-breadcrumb-item :to="{ path: '/' }">Home</el-breadcrumb-item>
        <el-breadcrumb-item :to="{ name: 'CategorizedItems', params: { categoryId: selectedItem.category_id }}">
          {{ selectedItem.category.name }}
        </el-breadcrumb-item>
        <el-breadcrumb-item>{{ selectedItem.name }}</el-breadcrumb-item>
      </el-breadcrumb>
      <br>
      <el-row :gutter="10">
        <el-col :xs="24" :sm="12" :md="10">
          <div>
            <figure class="zoom-img" :style="`background-image: url(${selectedImg})`" @mousemove="zoom">
              <img :src="selectedImg">
            </figure>
          </div>
          <div>
            <img v-for="(itemMedia, index) in selectedItem.media" :key="index" :src="itemMedia.thumbnail" width="100" style="padding: 10px 5px 0 0; cursor: pointer" @click="selectedImg = itemMedia.link">
          </div>
        </el-col>
        <el-col :xs="24" :sm="12" :md="14">
          <small>{{ selectedItem.category.name }}</small>
          <h3>{{ selectedItem.name }}</h3>
          <p>{{ selectedItem.description }}</p>
          <h2>{{ '₦' + formatNumber(selectedItem.price.amount, 2) }}</h2>
          <div v-if="available_colors.length > 0">
            <span><label>Colors: </label></span>
            <span v-for="(color, index) in available_colors" :key="index">
              <span :style="`background: ${color}; padding: 5px 15px 5px 15px; margin: 5px; cursor: pointer; border-radius: 30px;`" @click="setItemDetailsForCart(color);"><i v-if="selectedColor === color" class="el-icon-check" /></span> {{ color }}
            </span>
          </div>
          <br>
          <div v-if="stock_details.length > 0">
            <div v-if="available_details.length > 0">
              <label>Sizes: </label>
            </div>
            <table class="table table-bordered">
              <tbody>
                <tr v-for="(stock, index) in stock_details" :key="index" style="cursor: pointer;">
                  <td @click="productForCart(stock, stock.size)">
                    <span v-if="stock.size !== null">
                      <span>{{ stock.size }}</span>
                    </span>
                    <span>
                      <el-badge v-if="parseInt(stock.quantity_stocked - stock.sold) > 0" :value="`${parseInt(stock.quantity_stocked - stock.sold)} in stock`" type="success" />
                      <el-badge v-else value="Out of Stock" type="danger" />
                    </span>
                    <span><i v-if="selectedProductStock.id === stock.id" class="el-icon-check" /></span>
                  </td>
                </tr>
              </tbody>
            </table>
            <hr>
            <el-button @click="addItemToWishlist(selectedItem)"><i class="fas fa-heart" /> Add to wishlist</el-button>
            <p />
            <!-- <el-button @click="addItemToComparedItems(selectedItem)"><i class="fas fa-random" /> Compare</el-button> -->
            <el-input-number v-model="quantity" :min="1" />
            <el-button :disabled="selectedItem.item_stocks.length < 1" type="primary" @click="addItemToCart(selectedItem)"><i class="el-icon-shopping-cart-2" /> Add to Cart</el-button>
            <el-alert v-if="showQuantityOverflowError" type="error">Quantity is more than stock</el-alert>
          </div>
        </el-col>
      </el-row>

      <br>
      <br>
      <br>
      <el-tabs type="card">
        <el-tab-pane>
          <span slot="label">Related Products</span>
          <related-products :category-id="selectedItem.category.id" :exclude-item-id="selectedItem.id" />
        </el-tab-pane>
        <el-tab-pane>
          <span slot="label">Reviews</span>
          <div v-loading="loadReview">
            <div v-if="totalReviews > 0" style="border: double #cccccc; border-radius: 10px; padding: 15px">
              <el-row :gutter="20">
                <el-col :xs="24" :sm="24" :md="16">
                  <div style="height: 300px;">
                    <el-row v-for="(review, review_index) in reviews" :key="review_index">
                      <el-col :xs="24" :sm="24" :md="8">
                        <img src="/images/no-image.png" width="50"><br>
                        {{ review.user.name }}
                        <el-rate
                          v-model="review.star"
                          disabled
                          text-color="#ff9900"
                        />
                      </el-col>
                      <el-col :xs="24" :sm="24" :md="16">
                        <aside>{{ review.comment }}</aside>
                      </el-col>
                    </el-row>
                  </div>
                  <pagination
                    v-show="totalReviews > 10"
                    :total="totalReviews"
                    :page.sync="query.page"
                    :limit.sync="query.limit"
                    @pagination="fetchReviews"
                  />
                </el-col>
                <el-col :xs="24" :sm="24" :md="8">
                  <h4>Based on {{ totalReviews }} reviews</h4>
                  <h2>{{ formatNumber(overallReview, 1) }}</h2>
                  <el-rate
                    v-model="overallReview"
                    disabled
                  />
                  <p>Overall</p>
                </el-col>
              </el-row>
            </div>
            <div v-else style="border: double #cccccc; border-radius: 10px; padding: 15px">
              <el-alert
                title="There are no reviews yet. Buy this product and give a review"
                type="error"
                effect="dark"
                :closable="false"
              />
            </div>
          </div>
        </el-tab-pane>
      </el-tabs>
    </div>
    <div v-if="!selectedItem && load === false">
      <error-404 />
    </div>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination';
import { formatNumber } from '@/utils/index';
import RelatedProducts from './partials/RelatedProducts.vue';
import Error404 from '@/views/error-page/404';
import Resource from '@/api/resource';
export default {
  name: 'ProductDetails',
  components: {
    Pagination,
    RelatedProducts,
    Error404,
  },
  data() {
    return {
      load: false,
      select: 1,
      categories: [],
      selectedItem: {
        selectedColor: '',
        selectedSize: '',
      },
      selectedImg: '',
      showQuantityOverflowError: false,
      quantity: 1,
      available_colors: [],
      available_details: [],
      stock_details: [],
      selectedColor: '',
      selectedSize: '',
      selectedDetail: '',
      selectedProductStock: '',
      reviews: [],
      totalReviews: 0,
      overallReview: 0,
      query: {
        page: 1,
        limit: 10,
      },
      loadReview: false,
    };
  },
  created() {
    this.fetchItem();
    this.fetchReviews();
  },
  methods: {
    formatNumber,
    quantityOverflow(quantity){
      const app = this;
      app.showQuantityOverflowError = false;
      const stock = app.selectedProductStock;
      const stockBalance = parseInt(stock.quantity_stocked - stock.sold);
      if (quantity > stockBalance) {
        app.showQuantityOverflowError = true;
        return true;
      }
      return false;
    },
    calculateDiscounts(item, quantity){
      const standardAmount = item.price.amount;
      item.rate = standardAmount;
      item.standardAmount = standardAmount;
      let price = parseInt(quantity * standardAmount);
      let discountedAmount = standardAmount;
      if (item.discounts.length > 0) {
        item.discounts.forEach(discount => {
          const moq = discount.minimum_order_quantity;
          if (quantity >= moq) {
            discountedAmount = discount.amount;
            item.rate = discountedAmount;
            price = parseInt(quantity * discountedAmount);
          }
        });
      }
      item.subTotal = price;
      return item;
    },
    addItemToCart(item) {
      const app = this;
      const quantity = (app.quantity > 0) ? app.quantity : 1;
      const stock = app.selectedProductStock;
      const stockBalance = parseInt(stock.quantity_stocked - stock.sold);
      if (app.quantityOverflow(quantity)) {
        item.quantity = stockBalance;
        return false;
      }
      item = app.calculateDiscounts(item, quantity);
      const new_name = `${item.name} - ${app.selectedColor} - ${app.selectedSize}`;
      const param = {
        id: item.id,
        stock_id: stock.id,
        quantity: quantity,
        media: item.media,
        rate: item.rate,
        subTotal: item.subTotal,
        standardAmount: item.standardAmount,
        name: new_name,
      };
      app.$store.dispatch('order/addItemToCart', param);
      app.$notify({
        title: `${item.name} is added to cart`,
      });
    },
    addItemToWishlist(item) {
      const app = this;
      item.quantity = 1;
      item.new_name = `${item.name} - ${app.selectedColor} - ${app.selectedSize}`;
      app.$store.dispatch('order/addItemToWishlist', item);
      app.$notify({
        title: `${item.name} is added to wish list`,
      });
    },
    addItemToComparedItems(item) {
      item.quantity = 1;
      this.$store.dispatch('order/addItemForComparison', item);
      this.$notify({
        title: `${item.name} is added for comparison`,
      });
    },
    zoom(e){
      var zoomer = e.currentTarget;
      const offsetX = (e.offsetX) ? e.offsetX : e.touches[0].pageX;
      const offsetY = (e.offsetY) ? e.offsetY : e.touches[0].pageX;
      const x = offsetX / zoomer.offsetWidth * 100;
      const y = offsetY / zoomer.offsetHeight * 100;
      zoomer.style.backgroundPosition = x + '% ' + y + '%';
    },
    fetchItem() {
      const app = this;
      const slug = app.$route.params.slug;
      app.load = true;

      const itemCategory = new Resource('item-details');
      itemCategory.list({ slug })
        .then(response => {
          app.selectedItem = response.item;
          app.selectedItem.selectedColor = '';
          app.selectedItem.selectedSize = '';
          if (response.item) {
            app.selectedImg = (response.item.media.length > 0) ? response.item.media[0].link : '/images/no-image.jpeg';

            app.setOtherItemDescription();
          }
          app.load = false;
        })
        .catch(error => {
          app.load = false;
          console.log(error);
        });
    },
    fetchReviews() {
      const param = this.query;
      const { limit, page } = param;
      param.item_id = this.$route.params.id;
      this.loadReview = true;
      const reviewsResource = new Resource('item-reviews');
      reviewsResource
        .list(param)
        .then((response) => {
          this.overallReview = parseFloat(response.average.overall);
          this.reviews = response.reviews.data;
          this.reviews.forEach((element, index) => {
            element['index'] = (page - 1) * limit + index + 1;
          });
          this.totalReviews = response.reviews.total;
          this.loadReview = false;
        })
        .catch((error) => {
          console.log(error);
          this.load = false;
        });
    },
    setOtherItemDescription() {
      const app = this;
      const colors = [];
      const itemStocks = app.selectedItem.item_stocks;
      itemStocks.forEach(stock => {
        if (stock.color !== null) {
          colors.push(stock.color);
        }
      });

      const uniqueColors = [...new Set(colors)];
      app.available_colors = uniqueColors;
      if (uniqueColors.length > 0) {
        app.selectedColor = uniqueColors[0];
        app.setItemDetailsForCart(app.selectedColor);
      }
    },
    setItemDetailsForCart(color) {
      const app = this;
      app.showQuantityOverflowError = false;
      const details = [];
      const itemStocks = app.selectedItem.item_stocks;
      const stock_details = itemStocks.filter(stock => stock.color === color);
      app.stock_details = stock_details;
      app.selectedColor = color;

      stock_details.forEach(stock => {
        if (stock.size !== null) {
          details.push(stock.size);
          app.selectedSize = stock.size;
        }
      });
      app.available_details = details;
      if (details.length > 0) {
        app.selectedSize = details[0];
      }
      app.productForCart(stock_details[0], app.selectedSize);
      // app.available_sizes = sizes;
    },
    productForCart(stock, size) {
      const app = this;
      app.selectedProductStock = stock;
      app.selectedSize = (size !== null) ? size : '';
    },

  },
};
</script>
<style>
  @import "~@/styles/public/search-bar.scss";
  figure.zoom-img {
    background-position: 50% 50%;
    position: relative;
    width: 350px;
    overflow: hidden;
    cursor: zoom-in;
  }
  figure.zoom-img img:hover {
    opacity: 0;
  }
  figure.zoom-img img {
    transition: opacity .5s;
    display: block;
    width: 100%;
  }

</style>
