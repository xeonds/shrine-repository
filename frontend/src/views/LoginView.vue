<template>
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
      <h1>Login</h1>
    </div>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Login</h4>
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
</template>

<style scoped>
</style>

<script>
import axios from "axios";

axios.defaults.baseURL = "http://www.jiujiuer.xyz/pages/repo-tr/";

export default {
  name: "LoginView",
  data: function () {
    return {
      user: {
        username: "",
        password: "",
      },
    };
  },
  methods: {
    login: async function () {
      let data = new FormData();

      data.append("uid", this.user.username);
      data.append("password", this.user.password);
      var res = await axios.post("index.php?api&v1&user&login", data);
      if (res.data.code == 200) {
        alert(res.data.msg);
        localStorage.setItem("userInfo", JSON.stringify(res.data.data));
        console.log(res.data);
        // window.location.href = "#/home";
      }
    },
  },
};
</script>