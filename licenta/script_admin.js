let navbar = document.querySelector(".header .navbar");
let accountBox = document.querySelector(".header .account-box");

document.querySelector("#menu-btn").onclick = () => {
  navbar.classList.toggle("active");
  accountBox.classList.remove("active");
};

document.querySelector("#user-btn").onclick = () => {
  accountBox.classList.toggle("active");
  navbar.classList.remove("active");
};

window.onscroll = () => {
  navbar.classList.remove("active");
  accountBox.classList.remove("active");
};

document.querySelector("#close-update").onclick = () => {
  document.querySelector(".edit-product-form").style.display = "none";
  window.location.href = "admin_products.php";
};

function searchProducts() {
  var input, filter, container, boxes, name, author, i;
  input = document.getElementById("searchInput");
  filter = input.value.toUpperCase();
  container = document.querySelector(".box-container");
  boxes = container.getElementsByClassName("box");

  for (i = 0; i < boxes.length; i++) {
    name = boxes[i].getElementsByClassName("name")[0];
    author = boxes[i].getElementsByClassName("author")[0];
    if (
      name.innerHTML.toUpperCase().startsWith(filter) ||
      author.innerHTML.toUpperCase().startsWith(filter)
    ) {
      boxes[i].style.display = "";
    } else {
      boxes[i].style.display = "none";
    }
  }
}
