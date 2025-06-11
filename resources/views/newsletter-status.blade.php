<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Subscription Status</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, transparent, rgba(103, 126, 234, 0.6), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .success-icon, .error-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            animation: scaleIn 0.5s ease-out;
        }

        .success-icon {
            background: linear-gradient(135deg, #4CAF50, #45a049);
        }

        .error-icon {
            background: linear-gradient(135deg, #f44336, #d32f2f);
        }

        @keyframes scaleIn {
            0% { transform: scale(0) rotate(180deg); opacity: 0; }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }

        .title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #333;
        }

        .success-title {
            color: #4CAF50;
        }

        .error-title {
            color: #f44336;
        }

        .message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .timer-section {
            background: rgba(103, 126, 234, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(103, 126, 234, 0.2);
        }

        .timer-text {
            font-size: 18px;
            color: #667eea;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .countdown {
            font-size: 32px;
            font-weight: 700;
            color: #667eea;
            font-family: 'Courier New', monospace;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: rgba(103, 126, 234, 0.2);
            border-radius: 3px;
            margin-top: 15px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 3px;
            transition: width 1s linear;
        }

        .btn {
            display: inline-block;
            padding: 14px 30px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(103, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(103, 126, 234, 0.6);
        }

        .btn-secondary {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .retry-section {
            background: rgba(244, 67, 54, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(244, 67, 54, 0.2);
        }

        .error-details {
            font-size: 14px;
            color: #999;
            margin-top: 15px;
            font-style: italic;
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
            }
            
            .title {
                font-size: 24px;
            }
            
            .countdown {
                font-size: 28px;
            }
            
            .btn {
                padding: 12px 24px;
                font-size: 14px;
            }
        }

        .floating-particles {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(103, 126, 234, 0.3);
            border-radius: 50%;
            animation: float 6s infinite linear;
        }

        .particle:nth-child(odd) {
            background: rgba(118, 75, 162, 0.3);
            animation-duration: 8s;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="floating-particles">
            <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
            <div class="particle" style="left: 20%; animation-delay: 1s;"></div>
            <div class="particle" style="left: 30%; animation-delay: 2s;"></div>
            <div class="particle" style="left: 40%; animation-delay: 3s;"></div>
            <div class="particle" style="left: 50%; animation-delay: 4s;"></div>
            <div class="particle" style="left: 60%; animation-delay: 5s;"></div>
            <div class="particle" style="left: 70%; animation-delay: 6s;"></div>
            <div class="particle" style="left: 80%; animation-delay: 7s;"></div>
            <div class="particle" style="left: 90%; animation-delay: 8s;"></div>
        </div>

        @if($isUnsubscribed)
            <!-- Success State -->
            <div class="success-icon">
                ✓
            </div>
            <h1 class="title success-title">Successfully Unsubscribed!</h1>
            <p class="message">
                You have been successfully removed from our mailing list. You will no longer receive emails from us.
            </p>
            
            <div class="timer-section">
                <div class="timer-text">Redirecting to homepage in:</div>
                <div class="countdown" id="countdown">10</div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressBar" style="width: 100%;"></div>
                </div>
            </div>

            <div>
                <a href="{{ url('/') }}" class="btn btn-primary" id="homeBtn">
                    Go to Homepage Now
                </a>
            </div>

        @else
            <!-- Failure State -->
            <div class="error-icon">
                ✕
            </div>
            <h1 class="title error-title">Unsubscribe Failed</h1>
            <p class="message">
                We encountered an issue while processing your unsubscribe request. This could be due to an invalid link or the subscription might have already been removed.
            </p>

            <div class="retry-section">
                <p><strong>What can you do?</strong></p>
                <ul style="text-align: left; margin-top: 10px; color: #666;">
                    <li>Check if the unsubscribe link is correct</li>
                    <li>Try refreshing the page</li>
                    <li>Contact our support team for assistance</li>
                </ul>
            </div>

            <div>
                <a href="javascript:window.location.reload()" class="btn btn-primary">
                    Try Again
                </a>
                <a href="{{ url('/') }}" class="btn btn-secondary">
                    Go to Homepage
                </a>
            </div>

            <div class="error-details">
                If the problem persists, please contact our support team with the error reference.
            </div>
        @endif
    </div>

    <script>
        @if($isUnsubscribed)
        // Countdown timer functionality
        let timeLeft = 10;
        const countdownEl = document.getElementById('countdown');
        const progressBar = document.getElementById('progressBar');
        
        const updateCountdown = () => {
            countdownEl.textContent = timeLeft;
            const progressWidth = (timeLeft / 10) * 100;
            progressBar.style.width = progressWidth + '%';
            
            if (timeLeft <= 0) {
                window.location.href = '{{ url("/") }}';
            } else {
                timeLeft--;
            }
        };

        // Start countdown
        const countdownInterval = setInterval(updateCountdown, 1000);

        // Clear countdown if user clicks the button
        document.getElementById('homeBtn').addEventListener('click', () => {
            clearInterval(countdownInterval);
        });
        @endif

        // Add smooth hover effects
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px) scale(1.05)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Add click ripple effect
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(255, 255, 255, 0.5)';
                ripple.style.transform = 'scale(0)';
                ripple.style.animation = 'ripple 0.6s linear';
                ripple.style.pointerEvents = 'none';
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Add ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>