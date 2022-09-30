<template>
  <div class="container-fluid" style="padding: 0">
    <div
      id="header"
      class="d-flex flex-column align-items-center justify-content-center w-100"
      style="padding: 2rem"
    >
      <h3>
        {{ config.ui.title }}<span class="text-primary">|</span
        ><span class="text-secondary">{{ config.ui.sub_title }}</span>
      </h3>
      <p v-if="user.status == 0">
        <router-link to="/user/register">Register</router-link> ·
        <router-link to="/user/login">Login</router-link>
      </p>
      <p v-else>
        <router-link to="/user/">User Center</router-link> ·
        <a href="#" @click="logout">Logout</a>
      </p>
    </div>
    <!-- Nav tabs -->
    <nav
      class="
        navbar navbar-expand
        bg-light
        navbar-light
        justify-content-start justify-content-sm-center
      "
    >
      <div class="navbar justify-content-sm-center">
        <ul class="navbar-nav">
          <li class="nav-item">
            <router-link class="nav-link" to="/">Home</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/tag/all">Tag</router-link>
          </li>
          <li :key="tag" class="nav-item" v-for="tag in config.ui.nav">
            <router-link class="nav-link" :to="'/tag/' + tag">{{
              tag
            }}</router-link>
          </li>
        </ul>
      </div>
    </nav>
    <!-- Main area -->
    <div
      id="main"
      class="d-flex flex-column align-items-center w-100"
      style="min-height: 80vh; padding: 2rem"
    >
      <router-view></router-view>
    </div>
    <!-- Footer area -->
    <div
      id="footer"
      class="
        d-flex
        flex-column
        align-items-center
        justify-content-center
        bg-light
      "
      style="padding: 2rem"
    >
      <p>
        &copy; 2021 <span class="text-primary">|</span> {{ config.ui.footer }}
      </p>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "HomeView",
  data: function () {
    return {
      user: {
        userid: "",
        username: "",
        password: "",
        apikey: "",
        status: 0,
      },
      meta: {
        metalist: [],
        taglist: [],
        view: [],
      },
      config: {
        ui: {
          title: "",
          nav: "",
          sub_title: "",
          footer: "",
          page: {
            comment_board: false,
            tags: false,
            archive: false,
          },
          login_entrance: false,
          open_link_in_new_tab: false,
        },
        meta: {
          comment_meta: false,
          file_detail: false,
          size_control: {
            full: "",
            wide: "",
            medium: "",
            small: "",
          },
        },
      },
      upload: {},
    };
  },
  created: async function () {
    var baseURL = "index.php?api&v1&";
    var tmp = "";
    var that = this;

    tmp = await axios.get(baseURL + "config&get");
    that.config = tmp.data.data;
    tmp = await axios.get(baseURL + "meta&get_meta");
    that.meta.metalist = tmp.data.data;
    that.getTagList();
    that.loadUserInfo();
  },
  methods: {
    getTagList: function () {
      var that = this;
      that.meta.metalist.forEach((element) => {
        element.tag.forEach((e) => {
          that.meta.taglist.push(e);
        });
      });
      that.meta.taglist = Array.from(new Set(that.meta.taglist));
    },
    isInclude: function (a, b) {
      var result = true;
      b.forEach((element) => {
        if (a.includes(element) == false) result = false;
      });
      return result;
    },
    loadUserInfo: function () {
      let userInfo = localStorage.getItem("userInfo");
      let that = this;

      if (null === userInfo) return;
      ({
        userid: that.user.userid,
        username: that.user.username,
        pasword: that.user.password,
        apikey: that.user.apikey,
      } = JSON.parse(userInfo));
      that.user.status = 1;
    },
    logout: function () {
      let that = this;

      that.user.status = 0;
      localStorage.clear();
      alert("logout success");
    },
  },
};
</script>
