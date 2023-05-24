import { get, set } from 'idb-keyval';
import store from '@/store';

function setItemsToDb(items, db) {
  set(db, (JSON.stringify(items)))
    .then().catch((err) => console.log('Cannots add item to cart!', err));
}
function setPendingOrder(order) {
  set('order', (JSON.stringify(order)))
    .then().catch((err) => console.log('Cannots set pending order!', err));
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
      case 'cart':
        store.dispatch('order/setCartItems', valid_items);
        break;
      case 'wish_list':
        store.dispatch('order/setWishlist', valid_items);
        break;
      case 'comparison':
        store.dispatch('order/setComparedItems', valid_items);
        break;
      default:
        break;
    }
  });
}
function fetchPendingOrder() {
  get('order').then((value) => {
    if (value) {
      const pending_order = JSON.parse(value);

      store.dispatch('order/setPendingOrder', pending_order);
    }
  });
}
const state = {
  cart: [],
  wishList: [],
  comparedItems: [],
  pendingOrder: {
    amount: 0,
    cart_items: [],
  },
};

const mutations = {
  SET_ITEM_TO_CART: (state, item) => {
    state.cart.push(item);
  },
  SET_CART: (state, items) => {
    state.cart = items;
  },
  ADD_ITEM_TO_WISHLIST: (state, item) => {
    state.wishList.push(item);
  },
  SET_WISHLIST: (state, items) => {
    state.wishList = items;
  },
  ADD_ITEM_FOR_COMPARISON: (state, item) => {
    state.comparedItems.push(item);
  },
  SET_COMPARED_ITEMS: (state, items) => {
    state.comparedItems = items;
  },
  SET_PENDING_ORDER: (state, order) => {
    Object.assign(state.pendingOrder, order);
  },
};

const actions = {
  addItemToCart({ commit }, item) {
    const carts = store.getters.cart;
    let count = 0;
    carts.forEach(cart => {
      if (cart.id === item.id) {
        count++;
      }
    });
    if (count < 1) {
      commit('SET_ITEM_TO_CART', item);
    }
    setItemsToDb(store.getters.cart, 'cart');
  },
  setCartItems({ commit }, items) {
    commit('SET_CART', items);
    setItemsToDb(store.getters.cart, 'cart');
  },
  addItemToWishlist({ commit }, item) {
    const wishLists = store.getters.wishList;
    let count = 0;
    wishLists.forEach(wishList => {
      if (wishList.id === item.id) {
        count++;
      }
    });
    // console.log(index);
    if (count < 1) {
      commit('ADD_ITEM_TO_WISHLIST', item);
    }
    setItemsToDb(store.getters.wishList, 'wish_list');
  },
  setWishlist({ commit }, items) {
    commit('SET_WISHLIST', items);
    setItemsToDb(store.getters.wishList, 'wish_list');
  },
  addItemForComparison({ commit }, item) {
    const comparedItems = store.getters.comparedItems;
    let count = 0;
    comparedItems.forEach(comparedItem => {
      if (comparedItem.id === item.id) {
        count++;
      }
    });
    // console.log(index);
    if (count < 0) {
      commit('ADD_ITEM_FOR_COMPARISON', item);
    }
    setItemsToDb(store.getters.comparedItems, 'comparison');
  },
  setComparedItems({ commit }, items) {
    commit('SET_COMPARED_ITEMS', items);
    setItemsToDb(store.getters.comparedItems, 'comparison');
  },
  setPendingOrder({ commit }, order) {
    commit('SET_PENDING_ORDER', order);
    setPendingOrder(store.getters.pendingOrder);
  },
  loadOfflineData() {
    return new Promise((resolve) => {
      fetchItemsInDb('cart');
      fetchItemsInDb('wish_list');
      fetchItemsInDb('comparison');
      fetchPendingOrder();
    });
  },
  // setNecessaryParams({ commit }) {
  //   const necessaryParams = new Resource('fetch-necessary-params');
  //   necessaryParams.list().then(response => {
  //     const params = response.params;
  //     commit('SET_PARAMS', params);
  //   });
  // },
};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
};
