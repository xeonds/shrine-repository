import "./plugins/bootstrap-vue";
import "./plugins/axios";
import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import hljs from "highlight.js";
import "highlight.js/styles/atom-one-light.css";

const app = createApp(App);

app.directive("highlight", function (el) {
  const blocks = el.querySelectorAll("pre code");
  blocks.forEach((block) => {
    hljs.highlightBlock(block);
  });
});

app.use(router).mount("#app");
