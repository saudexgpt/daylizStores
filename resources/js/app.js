import 'mdbvue/lib/css/mdb.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import 'mdbvue/lib/mdbvue.css';
import './styles/element-variables.scss';
import 'vue-mj-daterangepicker/dist/vue-mj-daterangepicker.css';
import '@/styles/index.scss'; // global css
// Import stylesheet
import 'vue-loading-overlay/dist/vue-loading.css';
import Vue from 'vue';
import IdleVue from 'idle-vue';
import Cookies from 'js-cookie';
import ElementUI from 'element-ui';
import App from './views/App';
import store from './store';
import router from '@/router';
import i18n from './lang'; // Internationalization
import '@/icons'; // icon
import '@/permission'; // permission control

import * as filters from './filters'; // global filters
import Loading from 'vue-loading-overlay';
import * as mdbvue from 'mdbvue';
import HighchartsVue from 'highcharts-vue';
import Highcharts from 'highcharts';
import drilldown from 'highcharts/modules/drilldown';
import exporting from 'highcharts/modules/exporting';
import highcharts3d from 'highcharts/highcharts-3d';
import highchartsMore from 'highcharts/highcharts-more';
import solidGauge from 'highcharts/modules/solid-gauge';
import stockInit from 'highcharts/modules/stock';
import { ServerTable, ClientTable, Event } from 'vue-tables-2';
import DateRangePicker from 'vue-mj-daterangepicker';
import Watermark from './watermark';
Vue.use(Watermark);
const eventsHub = new Vue();
Vue.use(IdleVue, {
  eventEmitter: eventsHub,
  store,
  idleTime: 1800000, // 1800 seconds i.e 30 mins
  startAtIdle: false,
});
Vue.use(HighchartsVue);

drilldown(Highcharts);
exporting(Highcharts);
highcharts3d(Highcharts);
highchartsMore(Highcharts);
solidGauge(Highcharts);
stockInit(Highcharts);
Vue.use(ClientTable);
Vue.use(ServerTable);
Vue.use(DateRangePicker);
Vue.use(Event);
Vue.use(Loading, {
  // props
  color: 'blue',
  // loader: 'dots',
  loader: 'spinner',
  canCancel: true,
  width: 40,
  height: 40,
  backgroundColor: '#ffffff',
  opacity: 0.3,
  zIndex: 999,
}, {
  // slots
});
// Registering all components
for (const component in mdbvue) {
  Vue.component(component, mdbvue[component]);
}
Vue.use(ElementUI, {
  size: Cookies.get('size') || 'medium', // set element-ui default size
  i18n: (key, value) => i18n.t(key, value),
});

// register global utility filters.
Object.keys(filters).forEach(key => {
  Vue.filter(key, filters[key]);
});

Vue.config.productionTip = false;

new Vue({
  el: '#app',
  router,
  store,
  i18n,
  render: h => h(App),

});
