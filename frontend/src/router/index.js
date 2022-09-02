import { createRouter, createWebHashHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import MetaView from "../views/MetaView.vue";
import MetaList from "../components/MetaList.vue";
import TagList from "../components/TagList.vue";

const routes = [
  { path: "/", redirect: "/home" },
  {
    path: "/home",
    component: HomeView,
    children: [
      {
        path: "tags",
        component: TagList,
      },
      {
        path: "tag/:id",
        component: MetaList,
      },
    ],
  },
  { path: "/meta/:meta_id", component: MetaView },
];

const router = createRouter({
  history: createWebHashHistory(),
  routes,
});

export default router;
