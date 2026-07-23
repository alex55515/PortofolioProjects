let userBox = document.querySelector(".header .header-2 .user-box");

document.querySelector("#user-btn").onclick = () => {
  userBox.classList.toggle("active");
  navbar.classList.remove("active");
};

let navbar = document.querySelector(".header .header-2 .navbar");

document.querySelector("#menu-btn").onclick = () => {
  navbar.classList.toggle("active");
  userBox.classList.remove("active");
};

window.onscroll = () => {
  userBox.classList.remove("active");
  navbar.classList.remove("active");

  if (window.scrollY > 400) {
    document.querySelector(".header .header-2").classList.add("active");
  } else {
    document.querySelector(".header .header-2").classList.remove("active");
  }
};

let slideIndex = 0;

function showSlide() {
  const slides = document.querySelectorAll(".carousel li");
  if (slideIndex >= slides.length) {
    slideIndex = 0;
  }
  if (slideIndex < 0) {
    slideIndex = slides.length - 1;
  }
  for (let i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }
  slides[slideIndex].style.display = "block";
}

function nextSlide() {
  slideIndex++;
  showSlide();
}

function prevSlide() {
  slideIndex--;
  showSlide();
}

showSlide();

function toggleFilters() {
  var filtersContainer = document.querySelector(".product-filters");
  filtersContainer.classList.toggle("product-filters");
}

function clearFilters() {
  var checkboxes = document.querySelectorAll('input[type="checkbox"]');
  checkboxes.forEach(function (checkbox) {
    checkbox.checked = false;
  });

  var searchInput = document.querySelector(".search-input");
  searchInput.value = "";
  document.getElementById("filter-form").submit();
}
