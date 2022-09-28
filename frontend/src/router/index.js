import { createRouter, createWebHashHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import MetaView from "../views/MetaView.vue";
import RegisterView from "../views/RegisterView.vue";
import LoginView from "../views/LoginView.vue";
import UserView from "../views/UserView.vue";
import MetaList from "../components/MetaList.vue";
import TagList from "../components/TagList.vue";
import OverView from "../components/OverView.vue";

const routes = [
  { path: "/", redirect: "/home/overview" },
  {
    path: "/home",
    component: HomeView,
    children: [
      {
        path: "overview",
        conponent: OverView,
      },
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
  { path: "/register", component: RegisterView },
  { path: "/login", component: LoginView },
  { path: "/user/:user_id", component: UserView },
];

const router = createRouter({
  history: createWebHashHistory(),
  routes,
});

export default router;
