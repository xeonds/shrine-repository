import { createRouter, createWebHashHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import MetaView from "../views/MetaView.vue";
import RegisterView from "../views/RegisterView.vue";
import LoginView from "../views/LoginView.vue";
import UserView from "../views/UserView.vue";
import MetaList from "../components/MetaList.vue";
import TagList from "../components/TagList.vue";
import OverView from "../components/OverView.vue";
import DataAnalysis from "../components/DataAnalysis.vue";

const routes = [
  {
    path: "/",
    component: HomeView,
    children: [
      {
        path: "",
        component: OverView,
      },
      { path: "tag/all", component: TagList },
      {
        path: "tag/:id",
        component: MetaList,
      },
      {
        path: ":user_id",
        component: UserView,
      },
      {
        path: "user",
        component: UserView,
        children: [{ path: "analysis", component: DataAnalysis }],
      },
      { path: "meta/:meta_id", component: MetaView },
    ],
  },
  { path: "/user/register", component: RegisterView },
  { path: "/user/login", component: LoginView },
];

const router = createRouter({
  history: createWebHashHistory(),
  routes,
});

export default router;
