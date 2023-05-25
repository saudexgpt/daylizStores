<template>
  <div>
    <el-autocomplete
      v-model="searchString"
      class="inline-input no-border my-autocomplete"
      :fetch-suggestions="fetchSuggestions"
      placeholder="Search for products"
      :trigger-on-focus="false"
      style="width: 100%;"
      @select="handleSearch"
    >
      <template slot-scope="{ item }">
        <span style="float: left">{{ item.name }}</span>
        <span style="float: right; color: #8492a6; font-size: 13px">{{ item.category.name }}</span>
      </template>
      <!-- <template slot="prepend">
        <el-select v-model="selectedCategory" placeholder="Category" @change="showCategorizedProducts">
          <el-option v-for="(category, category_index) in categories" :key="category_index" :label="category.name" :value="category.id" />
        </el-select>
      </template> -->
      <el-button slot="append" icon="el-icon-search" @click="load(`/product/details/${searchString}`)"><span class="hide-mobile">Search</span></el-button>
    </el-autocomplete>
  </div>

</template>

<script>
import Resource from '@/api/resource';
export default {
  name: 'SearchBox',
  components: {
  },
  data() {
    return {
      img: '/images/logo.png',
      showCartContent: false,
      selectedCategory: null,
      categories: [],
      searchString: '',
    };
  },
  computed: {
    allItems() {
      return this.$store.getters.allItems;
    },
  },
  created() {
    this.fetchItemCategories();
  },
  methods: {
    handleSearch(item) {
      const app = this;
      const slug = item.slug;
      const id = item.id;
      // app.$router.push({ path: `details/${slug}/${id}` });
      app.$router.push({ name: 'ProductDetails', params: { slug, id }});
    },
    showCategorizedProducts(categoryId) {
      this.$router.push({ name: 'CategorizedItems', params: { categoryId }});
    },
    fetchSuggestions(queryString, cb) {
      var items = this.allItems;
      var results = queryString ? items.filter(this.createFilter(queryString)) : items;
      // call callback function to return suggestions
      cb(results);
    },
    createFilter(queryString) {
      return (item) => {
        return (item.name.toLowerCase().indexOf(queryString.toLowerCase()) > -1);
      };
    },
    fetchItemCategories() {
      const app = this;
      const itemCategory = new Resource('menu-category');
      itemCategory.list()
        .then(response => {
        // app.categories = response.categories

          app.categories = response.categories;
        })
        .catch(error => {
          console.log(error);
        });
    },
    load(url) {
      this.$router.push({ path: url });
    },
  },
};
</script>
<style lang="scss" scoped>
  .my-autocomplete {
    li {
      line-height: normal;
      padding: 7px;

      .value {
        text-overflow: ellipsis;
        overflow: hidden;
      }
      .link {
        font-size: 12px;
        color: #b4b4b4;
      }
    }
  }
</style>

