<template>
  <div v-loading="load">
    <div class="text-center wow fadeInUp" data-wow-delay="0.1s" style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInUp;">
      <!-- <h3 class="section-title ff-secondary fw-normal">Shop</h3> -->
      <p>&nbsp;</p>
    </div>
    <carousel
      :autoplay="true"
      :autoplay-timeout="3000"
      :loop="true"
      :navigation-enabled="true"
      :per-page-custom="[[320, 1], [1199, 4]]"
    >
      <slide
        v-for="(item, index) in items"
        :key="index"
      >
        <div class="item-panel">

          <div>
            <router-link :to="{name: 'ProductDetails', params: { slug: item.slug, id: item.id}}">
              <small>{{ item.category.name }}</small>
              <div class="item-name">{{ item.name }}</div>
              <img v-if="item.media.length > 0" :src="item.media[0].thumbnail" class="item-image">
              <img v-else src="/images/no-image.jpeg" class="item-image">
            </router-link>
          </div>
          <span class="price-box">
            <br>
            <el-button class="pull-right" circle type="primary" @click="addItemToCart(item)"><i class="el-icon-shopping-cart-2" /></el-button>
            <div class="amount">{{ '₦' + formatNumber(item.price.amount, 2) }}</div>
            <br>
            <!-- <div class="zoom">
              <hr>
              <el-tooltip class="pull-right" effect="dark" content="Add to wishlist" placement="top-start">
                <el-button circle><i class="fas fa-heart" /></el-button>
              </el-tooltip>
              <el-tooltip class="item" effect="dark" content="Compare" placement="top-start">
                <el-button circle><i class="fas fa-random" /></el-button>
              </el-tooltip>
            </div> -->
          </span>
        </div>
      </slide>
    </carousel>
  </div>
</template>
<script>
import { Carousel, Slide } from 'vue-carousel';
import { formatNumber } from '@/utils/index';
import Resource from '@/api/resource';
export default {
  components: {
    Carousel,
    Slide,
  },
  props: {
    categoryId: {
      type: Number,
      default: () => null,
    },
    excludeItemId: {
      type: Number,
      default: () => null,
    },
  },
  data() {
    return {
      items: [],
      selectedItem: null,
      dialogVisible: false,
      load: false,
      query: {
        page: 1,
        limit: 10,
      },
      total: 0,
    };
  },
  watch: {
    categoryId() {
      this.fetchItems();
    },
  },
  created() {
    this.fetchItems();
  },
  methods: {
    formatNumber,
    addItemToCart(item) {
      item.quantity = 1;
      this.$store.dispatch('order/addItemToCart', item);
      this.$notify({
        title: `${item.name} is added to cart`,
      });
    },
    itemDetails(item){
      const app = this;
      const slug = item.slug;
      // const id = item.id;
      // app.$router.push({ path: `details/${slug}/${id}` });
      app.$router.push({ name: 'ProductDetails', params: { slug }});
    },
    fetchItems() {
      const app = this;
      const { limit, page } = app.query;
      const itemResource = new Resource('get-items');
      app.load = true;
      const param = app.query;
      param.category_id = app.categoryId;
      param.exclude_item_id = app.excludeItemId;
      itemResource.list(param)
        .then(response => {
          this.items = response.items.data;
          this.items.forEach((element, index) => {
            element['index'] = (page - 1) * limit + index + 1;
          });
          this.total = response.items.total;
          app.load = false;
        })
        .catch(error => {
          app.load = false;
          console.log(error);
        });
    },

  },
};
</script>
<style>
  @import "~@/styles/public/search-bar.scss";
</style>
