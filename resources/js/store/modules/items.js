import Resource from '@/api/resource';
import { get, set } from 'idb-keyval';
import store from '@/store';

function setItemsToDb(items, db) {
  set(db, (JSON.stringify(items)))
    .then().catch((err) => console.log('Cannots add item to cart!', err));
}

function fetchItemsInDb(db) {
  get(db).then((value) => {
    var valid_items = [];
    if (value) {
      // console.log(value)
      const unsaved_items = JSON.parse(value);

      // del('unsaved_customers')
      unsaved_items.forEach(item => {
        valid_items.push(item);
      });
    }
    switch (db) {
      case 'items':
        store.dispatch('items/commitItems', valid_items);
        break;
      case 'categories':
        store.dispatch('items/commitCategories', valid_items);
        break;
      default:
        break;
    }
  });
}
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
  commitItems({ commit }, items) {
    commit('SET_ITEMS', items);
  },
  commitCategories({ commit }, categories) {
    commit('SET_CATEGORIES', categories);
  },
  fetchAllItems({ commit }) {
    const itemResource = new Resource('all-items');
    itemResource.list()
      .then(response => {
        store.dispatch('items/commitItems', response.items);
        setItemsToDb(response.items, 'items');
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
        store.dispatch('items/commitCategories', response.categories);
        setItemsToDb(response.categories, 'categories');
      })
      .catch(error => {
        console.log(error);
      });
  },
  loadOfflineData() {
    return new Promise((resolve) => {
      fetchItemsInDb('items');
      fetchItemsInDb('categories');
    });
  },
};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
};
