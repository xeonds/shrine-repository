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
      <h1>Register</h1>
    </div>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Register</h4>
        </div>
        <div class="modal-body">
          <label>Username</label>
          <input
            type="text"
            name="username"
            class="form-control"
            v-model="user.username"
          />
          <label>Password</label>
          <input
            type="text"
            name="password"
            class="form-control"
            v-model="user.password"
          />
        </div>
        <div class="modal-footer">
          <button @click="register" class="btn btn-primary">Register</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
</style>

<script>
import axios from "axios";

export default {
  name: "RegisterView",
  data: function () {
    return {
      user: {
        username: "",
        password: "",
      },
    };
  },
  methods: {
    register: async function () {
      let data = new FormData();

      data.append("username", this.user.username);
      data.append("password", this.user.password);
      var res = await axios.post("index.php?api&v1&user&register", data);
      alert(res.data.msg);
      if (res.data.code == 200) {
        window.location.href = "#/home";
      }
    },
  },
};
</script>