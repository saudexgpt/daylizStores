<template>
  <div>
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
          <div v-if="available_colors.length > 0">
            <strong>Colors:</strong>
            <span style="border: solid 1px #cccccc; padding: 5px 10px 5px 10px; margin: 5px; cursor: pointer">
              <span @click="selectedItem.selectedColor = ''">X</span>
            </span>
            <span v-for="(color, index) in available_colors" :key="index" :style="`background: ${color}; padding: 5px 15px 5px 15px; margin: 5px; cursor: pointer`">
              <span @click="selectedItem.selectedColor = color"><i v-if="selectedItem.selectedColor === color" class="el-icon-check" /></span>
            </span>
          </div>
          <br>
          <div v-if="available_sizes.length > 0">
            <strong>Sizes: </strong>
            <span v-for="(size, index) in available_sizes" :key="index"><span @click="selectedItem.selectedSize = size">{{ size }}</span>, </span>
          </div>
          <div>
            <hr>
            <el-button @click="addItemToWishlist(selectedItem)"><i class="fas fa-heart" /> Add to wishlist</el-button>
            <!-- <el-button @click="addItemToComparedItems(selectedItem)"><i class="fas fa-random" /> Compare</el-button> -->
            <h2>{{ '₦' + formatNumber(selectedItem.price.amount, 2) }}</h2>
            <el-input-number v-model="quantity" :min="1" />
            <el-button type="primary" @click="addItemToCart(selectedItem)"><i class="el-icon-shopping-cart-2" /> Add to Cart</el-button>
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
      dialogVisible: false,
      quantity: 1,
      available_colors: [],
      available_sizes: [],
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
    addItemToCart(item) {
      item.quantity = (this.quantity > 0) ? this.quantity : 1;
      this.$store.dispatch('order/addItemToCart', item);
      this.$notify({
        title: `${item.name} is added to cart`,
      });
    },
    addItemToWishlist(item) {
      item.quantity = 1;
      this.$store.dispatch('order/addItemToWishlist', item);
      this.$notify({
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
          if (response.item) {
            app.selectedImg = response.item.media[0].link;

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
      const sizes = [];
      const itemStocks = app.selectedItem.item_stocks;
      itemStocks.forEach(stock => {
        if (stock.color !== null) {
          colors.push(stock.color);
        }
        if (stock.size !== null) {
          sizes.push(stock.size);
        }
      });
      app.available_colors = colors;
      app.available_sizes = sizes;
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
