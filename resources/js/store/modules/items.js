import Resource from '@/api/resource';
const state = {
  allItems: [],
  categories: [],
  latestProducts: [],
};

const mutations = {
  SET_ITEMS: (state, items) => {
    state.allItems = items;
  },
  SET_CATEGORIES: (state, categories) => {
    state.categories = categories;
  },
  SET_LATEST_PRODUCTS: (state, latestProducts) => {
    state.latestProducts = latestProducts;
  },
};

const actions = {
  fetchAllItems({ commit }) {
    const itemResource = new Resource('all-items');
    itemResource.list()
      .then(response => {
        commit('SET_ITEMS', response.items);
      })
      .catch(error => {
        console.log(error);
      });
  },

  fetchLatestProducts({ commit }) {
    const latestProductResource = new Resource('latest-products');
    latestProductResource.list().then(response => {
      commit('SET_LATEST_PRODUCTS', response.stocks.data);
    }).catch();
  },
  fetchCategories({ commit }) {
    const categoriesResource = new Resource('menu-category');
    categoriesResource.list()
      .then(response => {
        commit('SET_CATEGORIES', response.categories);
      })
      .catch(error => {
        console.log(error);
      });
  },
};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
};
