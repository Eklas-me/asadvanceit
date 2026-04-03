import { Controller, Post, Body, UnauthorizedException } from '@nestjs/common';
import { AuthService } from './auth.service';

@Controller('api/auth')
export class AuthController {
  constructor(private authService: AuthService) {}

  @Post('login')
  async login(@Body() body: any) {
    const user = await this.authService.validateUser(body.email, body.password);
    if (!user) {
      throw new UnauthorizedException('Invalid credentials');
    }
    return this.authService.login(user);
  }

  @Post('reset-password-direct')
  async resetPasswordDirect(@Body() body: any) {
    const success = await this.authService.resetPasswordDirect(body.email, body.password);
    if (!success) {
      throw new UnauthorizedException('Email not found');
    }
    return { message: 'Password reset successfully' };
  }
  @Post('check-email')
  async checkEmail(@Body('email') email: string) {
    const exists = await this.authService.checkEmail(email);
    if (!exists) {
      throw new UnauthorizedException('Email not found');
    }
    return { exists: true };
  }
}
