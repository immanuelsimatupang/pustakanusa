<?php
$title = "Reading Club Syndicate - Community of Literary Enthusiasts";
include '../partials/header.php';
?>

<!-- Hero Section -->
<section class="hero-section bg-dark text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Welcome to the Reading Club Syndicate</h1>
                <p class="lead mb-4">A secret society of books where literary minds gather, discuss, and discover.</p>
                <div class="d-flex gap-3">
                    <a href="join-discord.php" class="btn btn-gold btn-lg">Join Discord</a>
                    <a href="events/index.php" class="btn btn-outline-light btn-lg">Upcoming Events</a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="../assets/images/reading-club-syndicate-logo.png" alt="Reading Club Syndicate" class="img-fluid" style="max-width: 300px;">
            </div>
        </div>
    </div>
</section>

<!-- Community Highlights -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center border-0">
                    <div class="card-body">
                        <i class="fas fa-book-open fa-3x text-gold mb-3"></i>
                        <h3>Book Parties</h3>
                        <p class="text-muted">Join our silent reading sessions and literary discussions</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center border-0">
                    <div class="card-body">
                        <i class="fas fa-calendar-alt fa-3x text-gold mb-3"></i>
                        <h3>Events</h3>
                        <p class="text-muted">Participate in book clubs, author talks, and literary events</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center border-0">
                    <div class="card-body">
                        <i class="fas fa-heart fa-3x text-gold mb-3"></i>
                        <h3>Donations</h3>
                        <p class="text-muted">Support literacy initiatives and book drives</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trending Books -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2 class="text-center mb-5">Trending Recommendations</h2>
                <div class="row">
                    <!-- This will be populated by book recommendations -->
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <img src="https://via.placeholder.com/300x400" class="card-img-top" alt="Book Cover">
                            <div class="card-body">
                                <h5 class="card-title">The Midnight Library</h5>
                                <p class="card-text">A novel about regret, hope and second chances.</p>
                                <a href="shop/book-detail.php?id=1" class="btn btn-gold">Read More</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <img src="https://via.placeholder.com/300x400" class="card-img-top" alt="Book Cover">
                            <div class="card-body">
                                <h5 class="card-title">Educated</h5>
                                <p class="card-text">A memoir about education and family.</p>
                                <a href="shop/book-detail.php?id=2" class="btn btn-gold">Read More</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <img src="https://via.placeholder.com/300x400" class="card-img-top" alt="Book Cover">
                            <div class="card-body">
                                <h5 class="card-title">Sapiens</h5>
                                <p class="card-text">A brief history of humankind.</p>
                                <a href="shop/book-detail.php?id=3" class="btn btn-gold">Read More</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <img src="https://via.placeholder.com/300x400" class="card-img-top" alt="Book Cover">
                            <div class="card-body">
                                <h5 class="card-title">The Seven Husbands of Evelyn Hugo</h5>
                                <p class="card-text">A story about love, ambition, and secrets.</p>
                                <a href="shop/book-detail.php?id=4" class="btn btn-gold">Read More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../partials/footer.php'; ?>