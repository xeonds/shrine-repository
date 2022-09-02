<template>
  <div>
    <div class="container-fluid" style="padding: 0">
      <div
        id="header"
        class="
          d-flex
          flex-column
          align-items-center
          justify-content-center
          bg-light
          w-100
        "
        style="padding: 2rem"
      >
        <h3>
          {{ config.ui.title }}<span class="text-primary">|</span
          ><span class="text-secondary">{{ config.ui.sub_title }}</span>
        </h3>
      </div>
      <!-- Nav tabs -->

      <div
        id="main"
        class="d-flex flex-column align-items-center tab-content"
        style="padding: 2rem; min-height: 60vh"
      >
        <div>
          <ul
            class="nav nav-pills justify-content-center"
            style="padding: 1rem"
          >
            <li class="nav-item">
              <router-link class="nav-link" data-toggle="pill" to="/home"
                >Home</router-link
              >
            </li>
            <li class="nav-item">
              <router-link class="nav-link" data-toggle="pill" to="/home/tags"
                >Tag</router-link
              >
            </li>
            <li :key="tag" class="nav-item" v-for="tag in config.ui.nav">
              <router-link
                class="nav-link"
                data-toggle="pill"
                :to="'/home/tag/' + tag"
                >{{ tag }}</router-link
              >
            </li>
          </ul>
          <router-view></router-view>
        </div>
      </div>
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
          <a href="#" data-toggle="modal" data-target="#register">Register</a> |
          <a href="#" data-toggle="modal" data-target="#login">Login</a>
        </p>
        <p>&copy; 2021 <span class="text-primary">|</span> xeonds</p>
      </div>
    </div>
    <div class="modal fade" id="register">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Register</h4>
            <button type="button" class="close" data-dismiss="modal">
              &times;
            </button>
          </div>
          <form action="core.php?api&v1&user&register" method="post">
            <div class="modal-body">
              <label>Username</label>
              <input type="text" name="username" class="form-control" />
              <label>Password</label>
              <input type="text" name="password" class="form-control" />
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Register</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal fade" id="login">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Login</h4>
            <button type="button" class="close" data-dismiss="modal">
              &times;
            </button>
          </div>
          <div class="modal-body">
            <label>Username</label>
            <input
              type="text"
              name="uid"
              class="form-control"
              v-bind="user.username"
            />
            <label>Password</label>
            <input
              type="password"
              name="password"
              class="form-control"
              v-bind="user.password"
            />
          </div>
          <div class="modal-footer">
            <button @click="login" class="btn btn-primary">Login</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="createMeta">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Create Meta</h4>
            <button type="button" class="close" data-dismiss="modal">
              &times;
            </button>
          </div>
          <form
            action="core.php?api&v1&meta&create_meta"
            method="post"
            enctype="multipart/form-data"
          >
            <div class="modal-body">
              <input type="hidden" name="time" v-bind:value="getTimestamp()" />
              <label>Meta Type</label>
              <div>
                <label class="radio-inline"
                  ><input
                    type="radio"
                    name="type"
                    value="text"
                    v-model="upload.metaType"
                  />Text</label
                >
                <label class="radio-inline"
                  ><input
                    type="radio"
                    name="type"
                    value="file"
                    v-model="upload.metaType"
                  />File</label
                >
                <label class="radio-inline"
                  ><input
                    type="radio"
                    name="type"
                    value="metaArray"
                    v-model="upload.metaType"
                  />Meta Array</label
                >
              </div>
              <label>Tags</label>
              <input
                type="text"
                name="tag"
                class="form-control"
                placeholder="Use English ',' to split"
              />
              <label>UID</label>
              <input type="number" name="uid" class="form-control" />
              <template v-if="upload.metaType == 'text'">
                <label>Title</label>
                <input type="text" name="title" class="form-control" />
                <label>Content</label>
                <input type="text" name="content" class="form-control" />
              </template>
              <template v-if="upload.metaType == 'file'">
                <label>File</label>
                <input type="file" name="file" class="form-control" />
              </template>
              <template v-if="upload.metaType == 'metaArray'">
                <label>Meta Array</label>
                <input
                  type="text"
                  name="metaArray"
                  class="form-control"
                  placeholder="Use English ',' to split"
                />
              </template>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Create</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal fade" id="viewMeta">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{ meta.view.title }}</h4>
            <button type="button" class="close" data-dismiss="modal">
              &times;
            </button>
          </div>
          <div
            class="modal-body"
            id="view-meta"
            v-if="meta.view.type == 'text'"
            v-html="meta.view.content"
          ></div>
          <div class="modal-footer">
            <p>
              <span>{{ meta.view.type }}</span
              ><span class="text-primary"> · </span
              ><span :key="tag" v-for="tag in meta.view.tag"
                ><span class="text-primary">#</span>{{ tag }}&nbsp;</span
              >
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

axios.defaults.baseURL = "http://www.jiujiuer.xyz/pages/repo-tr/";

export default {
  name: "HomeView",
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
