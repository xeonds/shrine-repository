<template>
  <router-view></router-view>
</template>

<style>
body {
  background-image: url("assets/hero-bg.jpg");
  background-position: center;
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center center;
  background-attachment: fixed;
}
</style>

<script>
import axios from "axios";

export default {
  name: "App",
  components: {},
  data: function () {
    return {
      user: {
        userid: "",
        username: "",
        password: "",
        apikey: "",
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
  },
};
</script>

<style>
</style>
