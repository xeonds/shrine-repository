<template>
  <router-view></router-view>
</template>

<script>
import axios from "axios";
import marked from "marked";

axios.defaults.baseURL = "http://www.jiujiuer.xyz/pages/repo-tr/";

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
    var baseURL = "core.php?api&v1&";
    var tmp = "";
    var that = this;

    tmp = await axios.get(baseURL + "config&get");
    that.config = tmp.data.data;
    tmp = await axios.get(baseURL + "meta&get_meta");
    that.meta.metalist = tmp.data.data;
    that.getTagList();
  },
  methods: {
    getTimestamp: function () {
      return new Date().getTime();
    },
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
    viewMeta: function (meta) {
      var that = this;

      that.meta.view = Object.assign({}, meta);
      if (that.meta.view.type == "text") {
        that.meta.view.content = marked(that.meta.view.content);
      }
    },
    onSwitchMetaBox: function () {},
    login: async function () {
      var that = this;
      var data = new FormData();
      data.append("uid", that.user.username);
      data.append("password", that.user.password);
      var res = await axios.post("core.php?api&v1&user&login", data);
      if (res.data.code == 200) {
        that.user = res.data.data;
        console.log("login success");
      }
    },
  },
};
</script>

<style>
.metaBox {
  padding: 0.8rem;
  margin-top: 0.8rem;
  margin-bottom: 0.8rem;
  margin-left: 0.5rem;
  margin-right: 0.5rem;
  word-break: break-all;
  border-radius: 12px;
  background: #f2f2f2;
  box-shadow: 8px 8px 16px #e7e7e7, -8px -8px 16px #ffffff;
}
</style>
