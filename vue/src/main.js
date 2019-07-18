import Vue from 'vue'
import VueResource from 'vue-resource'
import App from './App'

import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
Vue.use(ElementUI);

import layer from 'vue-layer'

import router from './router'




Vue.prototype.$layer = layer(Vue)
Vue.config.productionTip = false
Vue.use(VueResource)

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  components: { App },
  template: '<App/>'
})
