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
import MetaEditor from "../components/MetaEditor.vue";
import ConfigList from "../components/ConfigList.vue";

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
    ],
  },
  { path: "/meta/:meta_id", component: MetaView },
  {
    path: "/user",
    component: UserView,
    children: [
      { path: "", component: DataAnalysis },
      { path: "meta", component: MetaEditor },
      { path: "advanced", component: ConfigList },
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
