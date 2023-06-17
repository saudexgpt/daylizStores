<template>
  <div v-loading="load">
    <!-- <div class="text-center wow fadeInUp" data-wow-delay="0.1s" style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInUp;">
      <p>&nbsp;</p>
    </div> -->
    <div v-if="items.length > 0 && load === false">
      <el-row :gutter="5">
        <el-col
          v-for="(item, index) in items"
          :key="index"
          :xs="12"
          :sm="8"
          :md="md"
          :lg="lg"
        >
          <div class="item-panel">

            <div class="image-panel">
              <router-link :to="{name: 'ProductDetails', params: { slug: item.slug}}">
                <small>{{ item.category.name }}</small>
                <div class="item-name">{{ item.name }}</div>
                <img v-if="item.media.length > 0" :src="item.media[0].thumbnail" class="item-image">
                <img v-else src="/images/no-image.jpeg" class="item-image">

                <img v-if="item.item_stocks.length < 1" src="/images/out-of-stock.png" class="out-of-stock">
              </router-link>
            </div>
            <span class="price-box">
              <br>
              <div align="center">
                <div class="amount">{{ '₦' + formatNumber(item.price.amount, 2) }}</div>
              </div>
              <div>
                <hr>
                <el-tooltip class="pull-right" effect="dark" content="Add to wishlist" placement="top-start">
                  <el-button circle @click="addItemToWishlist(item)"><i class="fas fa-heart" /></el-button>
                </el-tooltip>
                <el-tooltip effect="dark" content="View Details" placement="top-start">
                  <el-button circle type="primary" @click="$router.push({name: 'ProductDetails', params: { slug: item.slug}})"><i class="el-icon-view" /> </el-button>
                </el-tooltip>
                <!-- <el-tooltip class="pull-right" effect="dark" content="Add to wishlist" placement="top-start">
                  <el-button circle @click="addItemToWishlist(item)"><i class="fas fa-heart" /></el-button>
                </el-tooltip>
                <el-tooltip class="item" effect="dark" content="Compare" placement="top-start">
                  <el-button circle @click="addItemToComparedItems(item)"><i class="fas fa-random" /></el-button>
                </el-tooltip> -->
              </div>
            </span>
          </div>
        </el-col>
      </el-row>
      <pagination
        v-show="total > 0"
        :total="total"
        :page.sync="query.page"
        :limit.sync="query.limit"
        @pagination="fetchItems"
      />
    </div>
    <div v-if="items.length < 1 && load === false">
      <error-404 />
    </div>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination';
import Error404 from '@/views/error-page/404';
import { formatNumber } from '@/utils/index';
import Resource from '@/api/resource';
export default {
  components: {
    Pagination,
    Error404,
  },
  props: {
    categoryId: {
      type: String,
      default: () => null,
    },
    lg: {
      type: Number,
      default: 6,
    },
    md: {
      type: Number,
      default: 8,
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
        limit: 20,
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
